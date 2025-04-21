@extends('layouts.admin')

@section('content')
<div class="attendance-list">
    <div class="attendance-header">
        <h2>{{ \Carbon\Carbon::parse($current_day)->format('Y年n月j日') }}の勤怠</h2>
    </div>
    <div class="month-selector">
        <a href="?date={{ $previous_day }}@if(request()->has('user_id'))&user_id={{ request()->input('user_id') }}@endif" class="day-link">前日</a>
        <span class="current-month">{{ \Carbon\Carbon::parse($current_day)->format('Y/m/d') }}</span>
        <a href="?date={{ $next_day }}@if(request()->has('user_id'))&user_id={{ request()->input('user_id') }}@endif" class="day-link">翌日</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>社員名</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                    <td>{{ floor($attendance->break_time / 60) }}:{{ str_pad($attendance->break_time % 60, 2, '0', STR_PAD_LEFT) }}</td>
                <td>{{ floor($attendance->work_time / 60) }}:{{ str_pad($attendance->work_time % 60, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection