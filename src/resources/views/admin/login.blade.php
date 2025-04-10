@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login__content">
    <div class="login-form__heading">
        <h2>管理者ログイン</h2>
    </div>
    <form class="login-form" action="/admin/login" method="post">
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
            <button type="submit" class="form__button-submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection