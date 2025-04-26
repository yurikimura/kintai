<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance.index');
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

    public function update(Request $request, $id)
    {
        $attendance = Attendance::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'start_break_time' => 'nullable|date_format:H:i',
            'end_break_time' => 'nullable|date_format:H:i',
            'remarks' => 'required|string|max:1000',
        ], [
            'remarks.required' => '備考を記入してください',
        ]);

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

        StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $id,
        ]);

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

        // if ($existingAttendance) {
        //     return response()->json([
        //         'message' => '既に出勤記録が存在します',
        //         'attendance' => $existingAttendance
        //     ], 400);
        // }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'start_time' => $now,
        ]);

        return response()->json([
            'message' => '出勤記録が完了しました',
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
            'start_break_time' => $now
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
            'break_time' => $attendance->break_time + $breakTime
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
            'end_time' => $now
        ]);

        return response()->json([
            'message' => '退勤記録が完了しました',
            'attendance' => $attendance
        ]);
    }

    public function getCurrentStatus()
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $now->format('Y-m-d'))
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['status' => 'not_working']);
        }

        if ($attendance->break_start_time && !$attendance->break_end_time) {
            return response()->json(['status' => 'on_break']);
        }

        return response()->json(['status' => 'working']);
    }
}
