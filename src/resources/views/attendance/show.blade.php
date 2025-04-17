@extends('layouts.app')

@section('content')
<div class="attendance-detail-container">
    <div class="attendance-header">
        <h2>勤怠詳細</h2>
    </div>
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
                    @if($attendance->status === 'pending')
                        <div class="form-value">{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}<span class="time-separator">〜</span>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</div>
                    @else
                        <input type="time" name="start_time" value="{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}" {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                        <span class="time-separator">〜</span>
                        <input type="time" name="end_time" value="{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}" {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label>休憩</label>
                <div class="time-range">
                    @if($attendance->status === 'pending')
                        <div class="form-value">{{ $attendance->start_break_time ? $attendance->start_break_time->format('H:i') : '' }}<span class="time-separator">〜</span>{{ $attendance->end_break_time ? $attendance->end_break_time->format('H:i') : '' }}</div>
                    @else
                        <input type="time" name="start_break_time" value="{{ $attendance->start_break_time ? $attendance->start_break_time->format('H:i') : '' }}" {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                        <span class="time-separator">〜</span>
                        <input type="time" name="end_break_time" value="{{ $attendance->end_break_time ? $attendance->end_break_time->format('H:i') : '' }}" {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label>備考</label>
                @if($attendance->status === 'pending')
                    <div class="form-value">{!! nl2br(e($attendance->remarks)) !!}</div>
                @else
                    <textarea class="note-box" name="remarks" rows="4">{{ $attendance->remarks }}</textarea>
                @endif
            </div>
        </div>
        <div class="form-actions">
            @if($attendance->status === 'pending')
                <p class="pending-message">※ 承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="edit-button">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection

@section('css')
<style>
.attendance-detail-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.attendance-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.status-label {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
}

.status-label.pending {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
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
    margin-top: 50px;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 20px;
}

.form-group:last-child {
    border-bottom: none;
}

.form-group label {
    display: block;
    margin-bottom: 0;
    min-width: 100px;
}

.form-value {
    font-size: 16px;
    padding: 8px 0;
    flex: 1;
    font-weight: bold;
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
    padding: 0 15px;
}

.note-box {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f8f8f8;
    resize: vertical;
    font-family: inherit;
    min-height: 60px;
}

.form-actions {
    margin-top: 20px;
    text-align: right;
}

.pending-message {
    color: #ff0000;
    margin: 20px 0;
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
@endsection