@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>スレッド一覧</h1>
        <a href="{{ route('threads.create') }}" class="btn btn-primary">新規作成</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @foreach($threads as $thread)
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ route('threads.show', $thread->id) }}" class="text-decoration-none">
                                {{ $thread->title }}
                            </a>
                        </h5>
                        <p class="card-text text-muted">
                            投稿者: {{ $thread->user->name }} |
                            投稿日時: {{ $thread->created_at->format('Y/m/d H:i') }}
                            @if ($thread->updated_at != $thread->created_at)
                                | 更新日時: {{ $thread->updated_at->format('Y/m/d H:i') }}
                            @endif
                        </p>
                        <p class="card-text">
                            {{ Str::limit($thread->content, 200) }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
