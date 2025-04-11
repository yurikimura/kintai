@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="?date={{ $previous_month }}" class="btn btn-outline-primary">&lt; 前月</a>
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($current_month)->format('Y年m月') }}の勤怠一覧</h3>
                        <a href="?date={{ $next_month }}" class="btn btn-outline-primary">翌月 &gt;</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>日付</th>
                                    <th>出勤時間</th>
                                    <th>退勤時間</th>
                                    <th>休憩開始</th>
                                    <th>休憩終了</th>
                                    <th>休憩時間</th>
                                    <th>勤務時間</th>
                                    <th>状態</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('Y-m-d') }}</td>
                                    <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                                    <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                                    <td>{{ $attendance->start_break_time ? $attendance->start_break_time->format('H:i') : '-' }}</td>
                                    <td>{{ $attendance->end_break_time ? $attendance->end_break_time->format('H:i') : '-' }}</td>
                                    <td>{{ $attendance->break_time }}分</td>
                                    <td>{{ $attendance->work_time }}分</td>
                                    <td>{{ $attendance->status }}</td>
                                    <td>
                                        <a href="{{ route('attendance.show', $attendance->id) }}" class="btn btn-sm btn-info">詳細</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
