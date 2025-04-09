<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function list()
    {
        $requests = StampCorrectionRequest::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
        return view('stamp_correction_request.list', compact('requests'));
    }
}
