@extends('layouts.app')

<style>
    .nav-tabs {
        display: flex;
        justify-content: flex-start;
        border-bottom: 1px solid #ddd;
    }
    .nav-item {
        margin-right: 10px;
    }
    .nav-link {
        border: 1px solid transparent;
        border-radius: 4px 4px 0 0;
        padding: 10px;
        color: #007bff;
        text-decoration: none;
    }
    .nav-link.active {
        border-color: #ddd #ddd #fff;
        background-color: #fff;
        color: #000;
    }
</style>

@section('content')
<div class="card">
    <div class="card-header">
        <h2>申請一覧</h2>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending">承認待ち</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved">承認済み</a>
            </li>
        </ul>
    </div>
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="pending">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日</th>
                        <th>申請理由</th>
                        <th>申請日</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests->where('status', 'pending') as $request)
                    <tr>
                        <td>{{ $request->status }}</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->request_date->format('Y-m-d') }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#requestModal{{ $request->id }}">
                                詳細
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($requests as $request)
<div class="modal fade" id="requestModal{{ $request->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">申請詳細</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tr>
                        <th>申請日</th>
                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>対象日</th>
                        <td>{{ $request->request_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>申請種別</th>
                        <td>{{ $request->request_type === 'start_time' ? '出勤時間' : '退勤時間' }}</td>
                    </tr>
                    <tr>
                        <th>現在の時間</th>
                        <td>{{ $request->current_time->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <th>修正後の時間</th>
                        <td>{{ $request->request_time->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <th>修正理由</th>
                        <td>{{ $request->reason }}</td>
                    </tr>
                    <tr>
                        <th>状態</th>
                        <td>{{ $request->status }}</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
