@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<style>
    .login-form__heading h2 {
        font-size: 32px; /* 文字サイズを大きく */
        font-weight: bold; /* 太字にする */
    }
    .form__group-title label {
        font-weight: bold; /* 太字にする */
    }
</style>
@endsection

@section('content')
<div class="login__content">
    <div class="login-form__heading">
        <h2>ログイン</h2>
    </div>
    <form class="login-form" action="/login" method="post">
        @csrf
        <div class="form__group">
            <div class="form__group-title">
                <label for="email">メールアドレス</label>
            </div>
            <div class="form__group-content">
                <input type="email" name="email" id="email" value="{{ old('email') }}">
            </div>
            @error('email')
            <div class="form__error">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="form__group">
            <div class="form__group-title">
                <label for="password">パスワード</label>
            </div>
            <div class="form__group-content">
                <input type="password" name="password" id="password">
            </div>
            @error('password')
            <div class="form__error">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="form__button">
            <button type="submit" class="form__button-submit">ログインする</button>
        </div>
        <div class="form__link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection