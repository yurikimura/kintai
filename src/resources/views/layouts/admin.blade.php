<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理システム</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance_admin.css') }}">
    @yield('css')
    <style>
        .header {
            background: #000;
            padding: 20px;
        }
        .header__inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1230px;
            margin: 0 auto;
        }
        .header__logo {
            height: 30px;
        }
        .header__nav {
            display: flex;
            gap: 20px;
        }
        .header__nav-link {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
    </style>
</head>
<body class="@yield('body_class')">
    <header class="header">
        <div class="header__inner">
            <a href="/attendance">
                <img src="{{ asset('img/logo.png') }}" alt="COACHTECH" class="header__logo">
            </a>
            @if(!Request::is('admin/login'))
            <nav class="header__nav">
                <a href="/admin/attendance/list" class="header__nav-link">勤怠一覧</a>
                <a href="/admin/staff/list" class="header__nav-link">スタッフ一覧</a>
                <a href="/admin/stamp-correction-requests/list" class="header__nav-link">申請一覧</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="header__nav-link" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">ログアウト</button>
                </form>
            </nav>
            @endif
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
