@extends('layouts.admin')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-header">
        <h2>{{ $staff->name }}さんの勤怠</h2>
    </div>
    <div class="month-selector">
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $previous_month]) }}" class="month-link">← 前月</a>
        <span class="current-month">{{ Carbon\Carbon::parse($current_month)->format('Y年m月') }}</span>
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $next_month]) }}" class="month-link">次月 →</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format('m/d') }} ({{ $attendance->date->isoFormat('ddd') }})</td>
                <td>{{ $attendance->start_time->format('H:i') }}</td>
                <td>{{ $attendance->end_time->format('H:i') }}</td>
                <td>{{ floor($attendance->break_time / 60) }}:{{ str_pad($attendance->break_time % 60, 2, '0', STR_PAD_LEFT) }}</td>
                <td>{{ floor($attendance->work_time / 60) }}:{{ str_pad($attendance->work_time % 60, 2, '0', STR_PAD_LEFT) }}</td>
                <td><a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
        </table>
    </div>
</div>

<style>
/* .attendance-list-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.list-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    border-left: 5px solid #000;
    padding-left: 10px;
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.attendance-table th, .attendance-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.attendance-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.attendance-table a {
    color: #007bff;
    text-decoration: none;
}

.attendance-table a:hover {
    text-decoration: underline;
} */
</style>
@endsection