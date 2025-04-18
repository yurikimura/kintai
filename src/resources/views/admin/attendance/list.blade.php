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
    <div class="attendance-table">
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
                    <td>{{ $attendance->break_time }}</td>
                    <td>{{ $attendance->work_time }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
.attendance-list {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.attendance-header {
    margin-bottom: 20px;
    border-left: 4px solid #000;
    padding-left: 10px;
}

.attendance-header h2 {
    font-size: 24px;
    margin: 0;
    font-weight: bold;
}

.month-selector {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 40px;
    margin: 30px 0;
}

.day-link {
    text-decoration: none;
    color: #000;
    font-weight: bold;
}

.current-month {
    font-size: 20px;
    font-weight: bold;
}

.attendance-table {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background-color: #fff;
    font-weight: normal;
    color: #666;
}

td {
    color: #333;
}

tr:hover {
    background-color: #f8f8f8;
}

.detail-link {
    color: #333;
    text-decoration: none;
}

.detail-link:hover {
    text-decoration: underline;
}
</style>
@endsection
