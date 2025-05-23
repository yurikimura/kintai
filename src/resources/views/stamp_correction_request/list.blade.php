@extends('layouts.app')

@section('content')
<div class="stamp-correction-request-list">
    <div class="stamp-correction-request-header">
        <h2>申請一覧</h2>
    </div>
    <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{ empty(request()->status) || request()->status === 'pending' ? 'active' : '' }}" id="pending-tab" href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->status === 'approved' ? 'active' : '' }}" id="approved-tab" href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}">承認済み</a>
            </li>
        </ul>
    <div class="">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                <tr>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                    <td>{{ $request->attendance->remarks ?: '申請理由なし' }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.show', ['id' => $request->attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection


