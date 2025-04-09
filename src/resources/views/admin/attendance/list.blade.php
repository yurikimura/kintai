@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="?date={{ $previous_date }}" class="btn btn-outline-primary">&lt; 前日</a>
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($current_date)->format('Y年m月d日') }}の勤怠一覧</h3>
                        <a href="?date={{ $next_date }}" class="btn btn-outline-primary">翌日 &gt;</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>従業員名</th>
                                    <th>出勤時間</th>
                                    <th>退勤時間</th>
                                    <th>休憩時間</th>
                                    <th>合計時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance['user_name'] }}</td>
                                    <td>{{ $attendance['start_time'] }}</td>
                                    <td>{{ $attendance['end_time'] }}</td>
                                    <td>{{ $attendance['break_time'] }}分</td>
                                    <td>{{ $attendance['total_time'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.attendance.show', $attendance['id']) }}" class="btn btn-info btn-sm">詳細</a>
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
