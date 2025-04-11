@extends('layouts.admin')

@section('content')
<div class="attendance-list-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <h1 class="attendance-date mb-4">{{ \Carbon\Carbon::parse($current_date)->format('Y年n月j日') }}の勤怠</h1>

                <div class="date-navigation-wrapper mb-5">
                    <div class="date-navigation d-inline-flex align-items-center">
                        <a href="?date={{ $previous_date }}" class="nav-link">
                            <span>← 前日</span>
                        </a>
                        <div class="current-date d-flex align-items-center">
                            <label for="date-picker" class="calendar-label mb-0">
                                <svg class="calendar-icon me-5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"/>
                                    <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                                </svg>
                            </label>
                            <input type="date" id="date-picker" class="date-picker" value="{{ $current_date }}" onchange="window.location.href = '?date=' + this.value">
                            <span>{{ \Carbon\Carbon::parse($current_date)->format('Y/m/d') }}</span>
                        </div>
                        <a href="?date={{ $next_date }}" class="nav-link">
                            <span>翌日 →</span>
                        </a>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table table-hover attendance-table">
                        <thead>
                            <tr>
                                <th class="border-top-0 text-center name-column">名前</th>
                                <th class="border-top-0 text-center time-column">出勤</th>
                                <th class="border-top-0 text-center time-column">退勤</th>
                                <th class="border-top-0 text-center time-column">休憩</th>
                                <th class="border-top-0 text-center time-column">合計</th>
                                <th class="border-top-0 text-center detail-column">詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td class="text-center name-column">{{ $attendance['user_name'] }}</td>
                                <td class="text-center time-column">{{ $attendance['start_time'] }}</td>
                                <td class="text-center time-column">{{ $attendance['end_time'] }}</td>
                                <td class="text-center time-column">{{ $attendance['break_time'] }}:00</td>
                                <td class="text-center time-column">{{ $attendance['total_time'] }}</td>
                                <td class="text-center detail-column">
                                    <a href="{{ route('admin.attendance.show', $attendance['id']) }}" class="detail-link">詳細</a>
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

<style>
.attendance-list-container {
    padding: 40px 0;
    background-color: #f5f5f5;
    min-height: 100vh;
}

.attendance-date {
    font-size: 1.8rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 2rem;
}

.date-navigation-wrapper {
    text-align: center;
    margin-bottom: 3rem;
}

.date-navigation {
    background: #fff;
    padding: 12px 130px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: inline-flex;
    align-items: center;
    gap: 20px;
}

.nav-link {
    color: #333;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.3s;
    white-space: nowrap;
}

.nav-link:hover {
    color: #666;
}

.calendar-icon {
    color: #666;
    width: 18px;
    height: 18px;
}

.current-date {
    font-size: 1.1rem;
    color: #333;
    font-weight: normal;
    padding: 0 20px;
    display: flex;
    align-items: center;
}

.attendance-table {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin: 0 auto;
    table-layout: fixed;
    width: auto;
}

.name-column {
    width: 120px;
}

.time-column {
    width: 90px;
}

.detail-column {
    width: 70px;
}

.attendance-table th {
    font-weight: normal;
    color: #666;
    padding: 15px;
    border-bottom: 1px solid #eee;
    font-size: 0.9rem;
    white-space: nowrap;
}

.attendance-table td {
    padding: 15px;
    vertical-align: middle;
    color: #333;
    border-bottom: 1px solid #eee;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attendance-table tbody tr:hover {
    background-color: #f8f9fa;
}

.detail-link {
    color: #333;
    text-decoration: none;
    font-size: 0.9rem;
}

.detail-link:hover {
    color: #666;
    text-decoration: underline;
}

.table-container {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    width: fit-content;
    margin: 0 auto;
    overflow-x: auto;
}

.attendance-table {
    margin: 0;
    table-layout: fixed;
    width: auto;
}

.calendar-label {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
}

.calendar-icon {
    color: #666;
    width: 18px;
    height: 18px;
    transition: color 0.3s;
}

.calendar-label:hover .calendar-icon {
    color: #333;
}

.date-picker {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
@endsection
