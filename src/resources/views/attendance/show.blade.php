@extends('layouts.header')

@section('content')
<div class="attendance-detail-container">
    <h2 class="detail-title">勤怠詳細</h2>

    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="attendance-card">
            <div class="form-group">
                <label>名前</label>
                <div class="form-value">{{ $attendance->user->name }}</div>
            </div>

            <div class="form-group">
                <label>日付</label>
                <div class="form-value">{{ $attendance->date->format('Y年m月d日') }}</div>
            </div>

            <div class="form-group">
                <label>出勤・退勤</label>
                <div class="time-range">
                    <input type="time" name="start_time" value="{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}">
                    <span class="time-separator">〜</span>
                    <input type="time" name="end_time" value="{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}">
                </div>
            </div>

            <div class="form-group">
                <label>休憩</label>
                <div class="time-range">
                    <input type="time" name="start_break_time" value="{{ $attendance->start_break_time ? $attendance->start_break_time->format('H:i') : '' }}">
                    <span class="time-separator">〜</span>
                    <input type="time" name="end_break_time" value="{{ $attendance->end_break_time ? $attendance->end_break_time->format('H:i') : '' }}">
                </div>
            </div>

            <div class="form-group">
                <label>備考</label>
                <textarea class="note-box" name="note" rows="4">{{ $attendance->remarks }}</textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="edit-button">修正</button>
        </div>
    </form>
</div>

<style>
.attendance-detail-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.detail-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 30px;
    border-left: 5px solid #000;
    padding-left: 10px;
}

.attendance-card {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
}

.form-value {
    font-size: 16px;
    padding: 8px 0;
}

.time-range {
    display: flex;
    align-items: center;
    gap: 10px;
}

.time-range input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f8f8f8;
}

.time-separator {
    color: #666;
}

.note-box {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f8f8f8;
    resize: vertical;
    font-family: inherit;
    font-size: 9px;
    min-height: 60px;
}

.form-actions {
    margin-top: 20px;
    text-align: right;
}

.edit-button {
    background: #000;
    color: #fff;
    padding: 10px 30px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.3s;
}

.edit-button:hover {
    opacity: 0.8;
}
</style>

@if (session('success'))
<div class="toast-container">
    <div class="toast show" role="alert">
        <div class="toast-header">
            <strong>通知</strong>
            <button type="button" class="toast-close" data-bs-dismiss="toast" aria-label="Close">×</button>
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
