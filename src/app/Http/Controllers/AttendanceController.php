<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
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
