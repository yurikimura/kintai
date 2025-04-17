@extends('layouts.admin')

@section('content')
<div class="staff-list-container">
    <div class="staff-list-header">
        <h2>スタッフ一覧</h2>
    </div>
    <div class="staff-table-wrapper">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.list', ['user_id' => $user->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
.staff-list-container {
    max-width: 800px;
    margin: 60px auto;
    padding: 0 20px;
}

.staff-list-header {
    border-left: 4px solid #000;
    padding-left: 12px;
    margin-bottom: 30px;
}

.staff-list-header h2 {
    font-size: 22px;
    font-weight: bold;
    margin: 0;
}

.staff-table-wrapper {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.staff-table {
    width: 100%;
    border-collapse: collapse;
}

.staff-table th,
.staff-table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.staff-table th {
    background-color: #f9f9f9;
    color: #333;
    font-weight: 500;
}

.staff-table td {
    color: #333;
}

.staff-table tr:last-child td {
    border-bottom: none;
}

.detail-link {
    color: #333;
    text-decoration: none;
    font-weight: bold;
}

.detail-link:hover {
    text-decoration: underline;
}
</style>
@endsection
