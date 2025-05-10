<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'start_break_time',
        'end_break_time',
        'break_time',
        'work_time',
        'status',
        'working_status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_break_time' => 'datetime',
        'end_break_time' => 'datetime',
    ];

    /**
     * ユーザーリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 勤務時間を「時間:分」の形式でフォーマットして返す
     *
     * @return string
     */
    public function getFormattedWorkTimeAttribute()
    {
        // データベースの work_time は分単位で保存されている
        $totalMinutes = $this->work_time;

        if ($totalMinutes === null) {
            return '--:--';
        }

        // 時間と分に分割
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        // HH:MM 形式にフォーマット
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * 休憩時間を「時間:分」の形式でフォーマットして返す
     *
     * @return string
     */
    public function getFormattedBreakTimeAttribute()
    {
        // データベースの break_time は分単位で保存されている
        $totalMinutes = $this->break_time;

        if ($totalMinutes === null) {
            return '--:--';
        }

        // 時間と分に分割
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        // HH:MM 形式にフォーマット
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * 出勤時間をHH:MM形式で返す
     */
    public function getStartTimeFormattedAttribute()
    {
        return $this->start_time ? $this->start_time->format('H:i') : '--:--';
    }

    /**
     * 退勤時間をHH:MM形式で返す
     */
    public function getEndTimeFormattedAttribute()
    {
        return $this->end_time ? $this->end_time->format('H:i') : '--:--';
    }

    /**
     * 休憩時間を計算する
     *
     * @return int 分単位の休憩時間
     */
    public function calculateBreakTime()
    {
        if (!$this->start_break_time || !$this->end_break_time) {
            return 0;
        }

        $totalMinutes = $this->end_break_time->diffInMinutes($this->start_break_time);
        return $totalMinutes;  // 単純に差分を返す
    }

    /**
     * 勤務時間を計算する
     *
     * @return int 分単位の勤務時間
     */
    public function calculateWorkTime()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        // 開始時間から終了時間までの総分数
        $totalMinutes = $this->end_time->diffInMinutes($this->start_time);

        // 休憩時間を引く
        return $totalMinutes - $this->break_time;
    }

    /**
     * モデルのブートメソッド
     * 保存前に自動計算を行う
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            // 出勤・退勤時間が設定されていれば勤務時間を計算
            if ($attendance->end_time) {
                $attendance->work_time = $attendance->calculateWorkTime();
            }

            // 休憩開始・終了時間が設定されていれば休憩時間を計算
            if ($attendance->start_break_time && $attendance->end_break_time) {
                $attendance->break_time = $attendance->calculateBreakTime();
            }
        });
    }
}