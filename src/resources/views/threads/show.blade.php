@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="mb-0">{{ $thread->title }}</h1>
            @if (Auth::id() === $thread->user_id)
                <a href="{{ route('threads.edit', $thread->id) }}" class="btn btn-primary">修正</a>
            @endif
        </div>

        <div class="card-body">
            <div class="mb-4">
                <p class="text-muted">
                    投稿者: {{ $thread->user->name }} |
                    投稿日時: {{ $thread->created_at->format('Y/m/d H:i') }}
                    @if ($thread->updated_at != $thread->created_at)
                        | 更新日時: {{ $thread->updated_at->format('Y/m/d H:i') }}
                    @endif
                </p>
            </div>

            <div class="thread-content">
                {!! nl2br(e($thread->content)) !!}
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('threads.index') }}" class="btn btn-secondary">戻る</a>
    </div>
</div>
@endsection
