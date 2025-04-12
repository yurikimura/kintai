@extends('layouts.app')

@section('content')
<div class="attendance-list">
    <div class="attendance-header">
        <h2>勤怠一覧</h2>
        <div class="month-selector">
            <a href="?date={{ $previous_month }}" class="month-link">&lt;</a>
            <span class="current-month">{{ \Carbon\Carbon::parse($current_month)->format('Y年m月') }}</span>
            <a href="?date={{ $next_month }}" class="month-link">&gt;</a>
        </div>
    </div>
    <div class="attendance-table">
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>勤務開始</th>
                    <th>勤務終了</th>
                    <th>休憩時間</th>
                    <th>勤務時間</th>
                    <th>状態</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date->format('m/d') }}</td>
                    <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->break_time }}:00</td>
                    <td>{{ $attendance->work_time }}:00</td>
                    <td>{{ $attendance->status }}</td>
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
}

.attendance-header h2 {
    font-size: 18px;
    margin-bottom: 15px;
}

.month-selector {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.month-link {
    text-decoration: none;
    color: #000;
}

.current-month {
    font-size: 16px;
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
</style>
@endsection
