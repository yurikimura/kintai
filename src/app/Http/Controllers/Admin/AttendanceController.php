<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $user_id = $request->input('user_id');

        if ($user_id) {
        // current_day のみの勤怠データを取得
        $attendances = Attendance::with('user')
                ->whereDate('date', $date)
                ->orderBy('user_id', 'asc')
                ->get();
        } else {
            // current_day のみの勤怠データを取得
            $attendances = Attendance::with('user')
                ->whereDate('date', $date)
                ->orderBy('user_id', 'asc')
                ->get();
        }

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'current_day' => $date->format('Y-m-d'),
            'previous_day' => $date->copy()->subDay()->format('Y-m-d'),
            'next_day' => $date->copy()->addDay()->format('Y-m-d'),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
    }

    public function staff($id)
    {
        $staff = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $id)
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.attendance.staff', [
            'staff' => $staff,
            'attendances' => $attendances,
        ]);
    }

    private function calculateTotalTime($attendance)
    {
        if (!$attendance->start_time || !$attendance->end_time) {
            return '-';
        }

        $start = Carbon::parse($attendance->start_time);
        $end = Carbon::parse($attendance->end_time);
        $total_minutes = $end->diffInMinutes($start) - ($attendance->break_time ?? 0);

        $hours = floor($total_minutes / 60);
        $minutes = $total_minutes % 60;

        return sprintf('%d時間%02d分', $hours, $minutes);
    }
}
