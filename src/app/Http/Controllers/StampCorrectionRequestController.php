<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\StampCorrectionRequestStoreRequest;

class StampCorrectionRequestController extends Controller
{
    public function store(StampCorrectionRequestStoreRequest $request)
    {
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

        $query = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if (empty($status) || $status === 'pending') {
            $query->whereHas('attendance', function ($q) {
                $q->whereIn('status', ['pending']);
            });
        } elseif ($status === 'approved') {
            $query->whereHas('attendance', function ($q) {
                $q->where('status', 'approved');
            });
        }

        $requests = $query->get();

        return view('stamp_correction_request.list', compact('requests', 'status'));
    }

    public function approve($id)
    {
        $stampCorrectionRequest = StampCorrectionRequest::findOrFail($id);

        // 勤怠データを更新
        $attendance = Attendance::findOrFail($stampCorrectionRequest->attendance_id);
        if ($stampCorrectionRequest->request_type === 'start_time') {
            $attendance->start_time = $stampCorrectionRequest->request_time;
        } else {
            $attendance->end_time = $stampCorrectionRequest->request_time;
        }
        $attendance->status = 'approved';
        $attendance->save();

        // 申請のステータスを更新
        $stampCorrectionRequest->status = 'approved';
        $stampCorrectionRequest->save();

        return redirect()->back()->with('success', '申請が承認されました。');
    }
}