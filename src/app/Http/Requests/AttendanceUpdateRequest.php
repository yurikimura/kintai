<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'start_break_time' => 'nullable|date_format:H:i|before_or_equal:end_time',
            'end_break_time' => 'nullable|date_format:H:i|after_or_equal:start_break_time|before_or_equal:end_time',
            'remarks' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください',
            'end_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'start_break_time.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_break_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_break_time.before_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
        ];
    }
}
