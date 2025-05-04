<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $days = ['日', '月', '火', '水', '木', '金', '土'];

        $dateTime = [
            'year' => $now->year,
            'month' => $now->month,
            'date' => $now->day,
            'day' => $days[$now->dayOfWeek],
            'hours' => $now->format('H'),
            'minutes' => $now->format('i')
        ];

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->format('Y-m-d'))
            ->latest()
            ->first();

        return view('attendance.index', compact('dateTime', 'attendance'));
    }

    public function list(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'current_month' => $date->format('Y-m'),
            'previous_month' => $date->copy()->subMonth()->format('Y-m'),
            'next_month' => $date->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::where('user_id', auth()->id())->findOrFail($id);
        return view('attendance.show', compact('attendance'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::where('user_id', auth()->id())->findOrFail($id);

        // 出勤時間と退勤時間の整合性チェック
        if ($request->filled('start_time') && $request->filled('end_time')) {
            $start = Carbon::parse($request->start_time);
            $end = Carbon::parse($request->end_time);
            if ($start->gt($end)) {
                return back()->withErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です'])->withInput();
            }
        }
        // 休憩開始時間が退勤時間より後の場合も同じバリデーション
        if ($request->filled('start_break_time') && $request->filled('end_time')) {
            $start_break = Carbon::parse($request->start_break_time);
            $end = Carbon::parse($request->end_time);
            if ($start_break->gt($end)) {
                return back()->withErrors(['start_break_time' => '出勤時間もしくは退勤時間が不適切な値です'])->withInput();
            }
        }
        // 休憩終了時間が退勤時間より後の場合も同じバリデーション
        if ($request->filled('end_break_time') && $request->filled('end_time')) {
            $end_break = Carbon::parse($request->end_break_time);
            $end = Carbon::parse($request->end_time);
            if ($end_break->gt($end)) {
                return back()->withErrors(['end_break_time' => '出勤時間もしくは退勤時間が不適切な値です'])->withInput();
            }
        }
        // 休憩開始時間が休憩終了時間より遅い場合のバリデーション
        if ($request->filled('start_break_time') && $request->filled('end_break_time')) {
            $start_break = Carbon::parse($request->start_break_time);
            $end_break = Carbon::parse($request->end_break_time);
            if ($start_break->gt($end_break)) {
                return back()->withErrors(['start_break_time' => '休憩開始時間もしくは休憩終了時間が不適切な値です'])->withInput();
            }
        }

        $attendance->update([
            'start_time' => $request->start_time ? Carbon::parse($request->start_time) : null,
            'end_time' => $request->end_time ? Carbon::parse($request->end_time) : null,
            'start_break_time' => $request->start_break_time ? Carbon::parse($request->start_break_time) : null,
            'end_break_time' => $request->end_break_time ? Carbon::parse($request->end_break_time) : null,
            'status' => 'pending',
            'remarks' => $request->remarks,
        ]);

        // 申請が存在しない場合のみ新規作成
        $existingStampCorrectionRequest = StampCorrectionRequest::where('user_id', auth()->id())
            ->where('attendance_id', $attendance->id)
            ->first();

        if (!$existingStampCorrectionRequest) {
            StampCorrectionRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
            ]);
        }


        return redirect()->route('attendance.show', $id)
            ->with('success', '勤怠情報を更新しました');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // 同じ日に既に出勤記録があるかチェック
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $now->format('Y-m-d'))
            ->whereNull('end_time')
            ->first();

        if ($existingAttendance) {
            return response()->json(['error' => '既に出勤記録が存在します'], 400);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'start_time' => $now,
            'working_status' => 'working',
        ]);

        return response()->json([
            'message' => '出勤を記録しました',
            'attendance' => $attendance
        ]);
    }

    public function startBreak(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $now->format('Y-m-d'))
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['error' => '出勤記録が見つかりません'], 404);
        }

        $attendance->update([
            'start_break_time' => $now,
            'working_status' => 'on_break'
        ]);

        return response()->json([
            'message' => '休憩開始を記録しました',
            'attendance' => $attendance
        ]);
    }

    public function endBreak(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $now->format('Y-m-d'))
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['error' => '出勤記録が見つかりません'], 404);
        }

        if (!$attendance->start_break_time) {
            return response()->json(['error' => '休憩開始の記録が見つかりません'], 400);
        }

        $breakTime = $attendance->start_break_time->diffInMinutes($now);

        $attendance->update([
            'end_break_time' => $now,
            'break_time' => $attendance->break_time + $breakTime,
            'working_status' => 'working'
        ]);

        return response()->json([
            'message' => '休憩終了を記録しました',
            'attendance' => $attendance
        ]);
    }

    public function end(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $now->format('Y-m-d'))
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['error' => '出勤記録が見つかりません'], 404);
        }

        $attendance->update([
            'end_time' => $now,
            'status' => 'pending',
            'working_status' => 'off'
        ]);

        // 申請が存在しない場合のみ新規作成
        $existingStampCorrectionRequest = StampCorrectionRequest::where('user_id', auth()->id())
            ->where('attendance_id', $attendance->id)
            ->first();

        if (!$existingStampCorrectionRequest) {
            StampCorrectionRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
            ]);
        }

        return response()->json([
            'message' => '退勤を記録しました',
            'attendance' => $attendance
        ]);
    }

    public function getCurrentStatus()
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $now->format('Y-m-d'))
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['status' => 'not_working']);
        }

        return response()->json(['status' => $attendance->working_status]);
    }
}
