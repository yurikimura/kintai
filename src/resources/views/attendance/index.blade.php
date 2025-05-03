@extends('layouts.app')

@section('css')
<style>
    .attendance-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 70px);
        background-color: #f5f5f5;
    }
    .attendance-content {
        text-align: center;
        padding: 40px;
    }
    .status-badge {
        display: inline-block;
        padding: 5px 20px;
        background-color: #ddd;
        border-radius: 20px;
        margin-bottom: 30px;
    }
    .date-display {
        font-size: 24px;
        margin-bottom: 20px;
    }
    .time-display {
        font-size: 64px;
        font-weight: bold;
        margin-bottom: 40px;
    }
    .button-container {
        display: flex;
        gap: 20px;
        justify-content: center;
    }
    .attendance-button {
        padding: 15px 60px;
        font-size: 20px;
        background-color: #000;
        color: #fff;
        border: none;
        border-radius: 20px;
        cursor: pointer;
    }
    .break-button, .break-return-button {
        padding: 15px 60px;
        font-size: 20px;
        background-color: #fff;
        color: #000;
        border: 1px solid #000;
        border-radius: 999px;
        cursor: pointer;
    }
    .attendance-button:hover, .break-button:hover, .break-return-button:hover {
        opacity: 0.8;
    }
    .hidden {
        display: none;
    }
    .thank-you-message {
        font-size: 24px;
        margin-top: 40px;
        color: #666;
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    .thank-you-message.show {
        opacity: 1;
    }
</style>
@endsection

@section('content')
@csrf
<div class="attendance-container">
    <div class="attendance-content">
        <div class="status-badge">勤務外</div>
        <div class="date-display" id="currentDate">{{ $dateTime['year'] }}年{{ $dateTime['month'] }}月{{ $dateTime['date'] }}日({{ $dateTime['day'] }})</div>
        <div class="time-display" id="currentTime">{{ $dateTime['hours'] }}:{{ $dateTime['minutes'] }}</div>
        <div class="button-container">
            <button class="attendance-button" id="startWork">出勤</button>
            <button class="attendance-button hidden" id="endWork">退勤</button>
            <button class="break-button hidden" id="startBreak">休憩入</button>
            <button class="break-return-button hidden" id="endBreak">休憩戻</button>
        </div>
        <div class="thank-you-message hidden" id="thankYouMessage">
            お疲れ様でした。
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = document.querySelector('input[name="_token"]').value;
    const startWorkBtn = document.getElementById('startWork');
    const endWorkBtn = document.getElementById('endWork');
    const startBreakBtn = document.getElementById('startBreak');
    const endBreakBtn = document.getElementById('endBreak');
    const dateDisplay = document.getElementById('currentDate');
    const timeDisplay = document.getElementById('currentTime');
    const statusBadge = document.querySelector('.status-badge');
    const thankYouMessage = document.getElementById('thankYouMessage');

    function updateDateTime() {
        const now = new Date();

        // 時刻の更新
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        timeDisplay.textContent = `${hours}:${minutes}`;
    }

    // ページロード時に実行
    updateDateTime();

    // 1分ごとに時刻を更新
    setInterval(updateDateTime, 60000);

    // ページロード時に現在の勤怠状態を取得
    function checkCurrentStatus() {
        fetch('/attendance/status', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('現在の勤怠状態:', data.status);
            updateUIByStatus(data.status);
        })
        .catch(error => {
            console.error('勤怠状態の取得中にエラーが発生しました:', error);
        });
    }

    function updateUIByStatus(status) {
        // すべてのボタンを一旦非表示に
        startWorkBtn.classList.add('hidden');
        endWorkBtn.classList.add('hidden');
        startBreakBtn.classList.add('hidden');
        endBreakBtn.classList.add('hidden');

        switch(status) {
            case 'working':
                endWorkBtn.classList.remove('hidden');
                startBreakBtn.classList.remove('hidden');
                statusBadge.textContent = '出勤中';
                break;
            case 'on_break':
                endBreakBtn.classList.remove('hidden');
                statusBadge.textContent = '休憩中';
                break;
            case 'off':
            case 'not_working':
            default:
                startWorkBtn.classList.remove('hidden');
                statusBadge.textContent = '勤務外';
                break;
        }
    }

    // ページロード時に状態を確認して適切なUIを表示
    checkCurrentStatus();

    startWorkBtn.addEventListener('click', function() {
        fetch('/attendance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            console.log('出勤記録が完了しました:', data);
            updateUIByStatus('working');
        })
        .catch(error => {
            console.error('出勤記録中にエラーが発生しました:', error);
            alert(error.message || '出勤記録に失敗しました。もう一度お試しください。');
        });
    });

    endWorkBtn.addEventListener('click', function() {
        fetch('/attendance/end', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            console.log('退勤記録の詳細:', data);
            updateUIByStatus('off');

            thankYouMessage.classList.remove('hidden');
            setTimeout(() => {
                thankYouMessage.classList.add('show');
            }, 100);
        })
        .catch(error => {
            console.error('退勤記録中にエラーが発生しました:', error);
            alert('退勤記録に失敗しました: ' + (error.error || '不明なエラー'));
        });
    });

    startBreakBtn.addEventListener('click', function() {
        fetch('/attendance/break/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('休憩開始を記録しました:', data);
            updateUIByStatus('on_break');
        })
        .catch(error => {
            console.error('休憩開始の記録中にエラーが発生しました:', error);
            alert('休憩開始の記録に失敗しました。もう一度お試しください。');
        });
    });

    endBreakBtn.addEventListener('click', function() {
        fetch('/attendance/break/end', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('休憩終了を記録しました:', data);
            updateUIByStatus('working');
        })
        .catch(error => {
            console.error('休憩終了の記録中にエラーが発生しました:', error);
            alert('休憩終了の記録に失敗しました。もう一度お試しください。');
        });
    });
});
</script>
