@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>勤怠詳細</h2>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>基本情報</h4>
                <table class="table">
                    <tr>
                        <th>日付</th>
                        <td>{{ $attendance->date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>出勤時間</th>
                        <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>退勤時間</th>
                        <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>休憩時間</th>
                        <td>{{ $attendance->break_time }}分</td>
                    </tr>
                    <tr>
                        <th>勤務時間</th>
                        <td>{{ $attendance->work_time }}分</td>
                    </tr>
                    <tr>
                        <th>状態</th>
                        <td>{{ $attendance->status }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h4>打刻修正申請</h4>
                <form action="{{ route('stamp_correction_request.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                    <div class="mb-3">
                        <label for="request_type" class="form-label">申請種別</label>
                        <select class="form-select" id="request_type" name="request_type" required>
                            <option value="start_time">出勤時間</option>
                            <option value="end_time">退勤時間</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="request_time" class="form-label">修正後の時間</label>
                        <input type="datetime-local" class="form-control" id="request_time" name="request_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">修正理由</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">申請する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
