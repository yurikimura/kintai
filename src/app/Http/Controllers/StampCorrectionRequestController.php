<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'request_type' => 'required|in:start_time,end_time',
            'request_time' => 'required|date',
            'reason' => 'required|string|max:1000',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        // 現在の時間を取得
        $currentTime = $request->request_type === 'start_time'
            ? $attendance->start_time
            : $attendance->end_time;

        $stampCorrectionRequest = StampCorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $request->attendance_id,
            'request_date' => Carbon::now()->format('Y-m-d'),
            'request_type' => $request->request_type,
            'current_time' => $currentTime,
            'request_time' => $request->request_time,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()
            ->back()
            ->with('success', '打刻修正申請を送信しました。');
    }

    public function list(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = StampCorrectionRequest::where('user_id', auth()->id())
            ->with('user')
            ->with('attendance');

        if ($status === 'pending') {
            $query->whereHas('attendance', function ($q) {
                $q->where('status', 'pending');
            });
        } elseif ($status === 'approved') {
            $query->whereHas('attendance', function ($q) {
                $q->where('status', 'approved');
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('stamp_correction_request.list', compact('requests', 'status'));
    }
}
