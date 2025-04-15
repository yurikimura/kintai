@extends('layouts.admin')

@section('content')
<div class="attendance-list-container">
    <h2 class="list-title">{{ $staff->name }}さんの勤怠</h2>
    <table class="attendance-table">
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
                <td>{{ $attendance->date->format('m/d (D)') }}</td>
                <td>{{ $attendance->start_time->format('H:i') }}</td>
                <td>{{ $attendance->end_time->format('H:i') }}</td>
                <td>{{ $attendance->break_time }}時間</td>
                <td>{{ $attendance->total_time }}時間</td>
                <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
.attendance-list-container {
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
}
</style>
@endsection
