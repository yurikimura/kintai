@extends('layouts.app')

@section('content')
<div class="attendance-list">
    <div class="attendance-header">
        <h2>勤怠一覧</h2>
    </div>
        <div class="month-selector">
            <div id="first-div">
                <a href="?date={{ $previous_month }}" class="month-link">前月</a>
            </div>
            <div id="second-div">
                <span class="current-month">{{ \Carbon\Carbon::parse($current_month)->format('Y/m') }}</span>
            </div>
            <div id="third-div">
                <a href="?date={{ $next_month }}" class="month-link">翌月</a>
            </div>
        </div>
    <div class="attendance-table">
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
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date->format('m/d') }}</td>
                    <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->break_time }}:00</td>
                    <td>{{ $attendance->work_time }}:00</td>
                    <td>
                        <a href="{{ route('attendance.show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
