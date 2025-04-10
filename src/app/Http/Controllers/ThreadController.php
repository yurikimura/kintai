<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThreadController extends Controller
{
    public function index()
    {
        $threads = Thread::with('user')->latest()->get();
        return view('threads.index', compact('threads'));
    }

    public function show($id)
    {
        $thread = Thread::with('user')->findOrFail($id);
        return view('threads.show', compact('thread'));
    }

    public function edit($id)
    {
        $thread = Thread::findOrFail($id);

        if ($thread->user_id !== Auth::id()) {
            return redirect()->route('threads.show', $id)
                ->with('error', '他のユーザーの投稿は編集できません。');
        }

        return view('threads.edit', compact('thread'));
    }

    public function update(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);

        if ($thread->user_id !== Auth::id()) {
            return redirect()->route('threads.show', $id)
                ->with('error', '他のユーザーの投稿は編集できません。');
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $thread->update($validated);

        return redirect()->route('threads.show', $id)
            ->with('success', '投稿を更新しました。');
    }
}
