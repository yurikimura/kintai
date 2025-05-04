<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now()->format('H:i:s'),
            'end_time' => null,
            'start_break_time' => null,
            'end_break_time' => null,
            'break_time' => 0,
            'work_time' => 0,
            'status' => 'pending',
            'working_status' => 'working',
        ];
    }
}