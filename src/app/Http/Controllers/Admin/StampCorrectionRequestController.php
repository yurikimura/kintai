<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = StampCorrectionRequest::with(['user', 'attendance'])
            ->orderByRaw('(SELECT date FROM attendances WHERE attendances.id = stamp_correction_requests.attendance_id) ASC');

        if ($status === 'pending') {
            $query->whereHas('attendance', function ($q) {
                $q->where('status', 'pending');
            });
        } elseif ($status === 'approved') {
            $query->whereHas('attendance', function ($q) {
                $q->where('status', 'approved');
            });
        }

        $requests = $query->get();

        return view('admin.stamp_correction_requests.list', compact('requests', 'status'));
    }

    public function show($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($id);

        return view('admin.stamp_correction_requests.show', compact('request'));
    }

    public function approve($attendance_id)
    {
        // 勤怠データを更新
        $attendance = Attendance::findOrFail($attendance_id);
        $attendance->status = 'approved';
        $attendance->save();

        return redirect()
            ->route('admin.stamp-correction-requests.index')
            ->with('success', '申請を承認しました。');
    }

    public function reject($id)
    {
        $stampCorrectionRequest = StampCorrectionRequest::findOrFail($id);
        $stampCorrectionRequest->status = 'rejected';
        $stampCorrectionRequest->save();

        return redirect()
            ->route('admin.stamp-correction-requests.index')
            ->with('success', '申請を却下しました。');
    }
}