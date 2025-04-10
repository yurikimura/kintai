@extends('layouts.app')

@section('content')
<div class="container">
    <h1>投稿を編集</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('threads.update', $thread->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="title">タイトル</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $thread->title) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="content">内容</label>
            <textarea class="form-control" id="content" name="content" rows="10" required>{{ old('content', $thread->content) }}</textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">更新</button>
            <a href="{{ route('threads.show', $thread->id) }}" class="btn btn-secondary">キャンセル</a>
        </div>
    </form>
</div>
@endsection
