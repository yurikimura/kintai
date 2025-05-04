@extends('layouts.header')

@section('body_class', 'register-page')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
<style>
    .auth-title {
        font-size: 32px; /* 文字サイズを大きく */
        font-weight: bold; /* 太字にする */
    }
    .form-group label {
        font-weight: bold; /* 太字にする */
    }
    .auth-button {
        display: block;
        width: 100%;
        text-align: center; /* 中央に配置 */
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <h2 class="auth-title">会員登録</h2>
    <form class="auth-form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
        </div>

        <button type="submit" class="auth-button">登録する</button>

        <div class="auth-link">
            <a href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection
