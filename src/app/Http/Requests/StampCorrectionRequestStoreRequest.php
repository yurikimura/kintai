<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequestStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'attendance_id' => 'required|exists:attendances,id',
            'request_type' => 'required|in:start_time,end_time',
            'request_time' => 'required|date',
            'reason' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'attendance_id.required' => '勤怠IDは必須です',
            'attendance_id.exists' => '指定された勤怠IDが存在しません',
            'request_type.required' => '申請種別は必須です',
            'request_type.in' => '申請種別が不正です',
            'request_time.required' => '申請時間は必須です',
            'request_time.date' => '申請時間の形式が不正です',
            'reason.required' => '理由は必須です',
            'reason.max' => '理由は1000文字以内で入力してください',
        ];
    }
}
