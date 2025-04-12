<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('attendances')->insert([
            [
                'user_id' => 1,
                'date' => now()->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'start_break_time' => '12:00:00',
                'end_break_time' => '13:00:00',
                'break_time' => 60,
                'work_time' => 720,
                'remarks' => '通常勤務',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'date' => now()->format('Y-m-d'),
                'start_time' => '09:30:00',
                'end_time' => '18:30:00',
                'start_break_time' => '12:30:00',
                'end_break_time' => '13:30:00',
                'break_time' => 60,
                'work_time' => 720,
                'remarks' => '通常勤務',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
