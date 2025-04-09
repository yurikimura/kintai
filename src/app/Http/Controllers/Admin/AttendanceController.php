<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $attendances = Attendance::with('user')
            ->whereDate('created_at', $date)
            ->get()
            ->map(function ($attendance) {
                return [
                    'user_name' => $attendance->user->name,
                    'start_time' => $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '-',
                    'end_time' => $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '-',
                    'break_time' => $attendance->break_time ?? 0,
                    'total_time' => $this->calculateTotalTime($attendance),
                    'id' => $attendance->id
                ];
            });

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'current_date' => $date->format('Y-m-d'),
            'previous_date' => $date->copy()->subDay()->format('Y-m-d'),
            'next_date' => $date->copy()->addDay()->format('Y-m-d'),
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
