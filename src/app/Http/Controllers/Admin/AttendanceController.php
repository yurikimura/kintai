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
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $attendances = Attendance::with('user')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->orderBy('user_id', 'asc')
            ->get();

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'current_month' => $date->format('Y-m'),
            'previous_month' => $date->copy()->subMonth()->format('Y-m'),
            'next_month' => $date->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
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
