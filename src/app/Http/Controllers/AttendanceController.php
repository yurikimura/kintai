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

    public function list()
    {
        $attendances = Attendance::where('user_id', auth()->id())->orderBy('date', 'desc')->get();
        return view('attendance.list', compact('attendances'));
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

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'start_time' => $now->format('H:i:s'),
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
            'break_start_time' => $now->format('H:i:s')
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

        $breakStart = Carbon::createFromFormat('H:i:s', $attendance->break_start_time);
        $breakEnd = $now;
        $breakTime = $breakStart->diffInMinutes($breakEnd);

        $attendance->update([
            'break_end_time' => $now->format('H:i:s'),
            'break_time' => $breakTime
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
            'end_time' => $now->format('H:i:s')
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
