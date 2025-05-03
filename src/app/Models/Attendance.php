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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateBreakTime()
    {
        if (!$this->start_break_time || !$this->end_break_time) {
            return 0;
        }

        $totalMinutes = $this->end_break_time->diffInMinutes($this->start_break_time);
        return $totalMinutes - $this->break_time;
    }


    public function calculateWorkTime()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $totalMinutes = $this->end_time->diffInMinutes($this->start_time);
        return $totalMinutes - $this->break_time;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            if ($attendance->end_time) {
                $attendance->work_time = $attendance->calculateWorkTime();
            }
        });
    }
}
