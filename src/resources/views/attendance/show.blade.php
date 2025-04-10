@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">勤怠詳細</h2>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">名前</th>
                            <td>{{ auth()->user()->name }}</td>
                        </tr>
                        <tr>
                            <th>年月日</th>
                            <td>{{ $attendance->date->format('Y年m月d日') }}</td>
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
                            <th>備考</th>
                            <td>{{ $attendance->remarks ?? '-' }}</td>
                        </tr>
                    </table>

                    <button type="button" class="btn btn-dark text-white" data-bs-toggle="modal" data-bs-target="#correctionModal">
                        修正
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 修正モーダル -->
<div class="modal fade" id="correctionModal" tabindex="-1" aria-labelledby="correctionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="correctionModalLabel">勤怠修正申請</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('stamp_correction_request.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

                    <div class="mb-3">
                        <label for="request_type" class="form-label">修正項目</label>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                    <button type="submit" class="btn btn-primary">申請する</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">通知</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // トースト通知の自動非表示
    const toastElList = document.querySelectorAll('.toast');
    toastElList.forEach(function(toastEl) {
        setTimeout(function() {
            const toast = new bootstrap.Toast(toastEl);
            toast.hide();
        }, 3000);
    });
});
</script>
@endsection
