<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
            'end_time' => 'nullable|date_format:H:i',
            'start_break_time' => 'nullable|date_format:H:i',
            'end_break_time' => 'nullable|date_format:H:i',
            'remarks' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'start_time.date_format' => '出勤時間は正しい時間形式（HH:mm）で入力してください',
            'end_time.date_format' => '退勤時間は正しい時間形式（HH:mm）で入力してください',
            'start_break_time.date_format' => '休憩開始時間は正しい時間形式（HH:mm）で入力してください',
            'end_break_time.date_format' => '休憩終了時間は正しい時間形式（HH:mm）で入力してください',
            'remarks.required' => '備考を記入してください',
            'remarks.max' => '備考は1000文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 出勤時間と退勤時間の整合性チェック
            if ($this->filled('start_time') && $this->filled('end_time')) {
                $start = Carbon::parse($this->start_time);
                $end = Carbon::parse($this->end_time);
                if ($start->gt($end)) {
                    $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 休憩開始時間が退勤時間より後の場合
            if ($this->filled('start_break_time') && $this->filled('end_time')) {
                $start_break = Carbon::parse($this->start_break_time);
                $end = Carbon::parse($this->end_time);
                if ($start_break->gt($end)) {
                    $validator->errors()->add('start_break_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 休憩終了時間が退勤時間より後の場合
            if ($this->filled('end_break_time') && $this->filled('end_time')) {
                $end_break = Carbon::parse($this->end_break_time);
                $end = Carbon::parse($this->end_time);
                if ($end_break->gt($end)) {
                    $validator->errors()->add('end_break_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 休憩開始時間が休憩終了時間より遅い場合
            if ($this->filled('start_break_time') && $this->filled('end_break_time')) {
                $start_break = Carbon::parse($this->start_break_time);
                $end_break = Carbon::parse($this->end_break_time);
                if ($start_break->gt($end_break)) {
                    $validator->errors()->add('start_break_time', '休憩開始時間もしくは休憩終了時間が不適切な値です');
                }
            }
        });
    }
}
