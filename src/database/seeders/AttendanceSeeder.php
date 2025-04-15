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
        $startDate = now()->subMonth();
        $endDate = now();

        $attendances = [];

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $attendances[] = [
                'user_id' => 1,
                'date' => $date->format('Y-m-d'),
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'start_break_time' => '12:00:00',
                'end_break_time' => '13:00:00',
                'break_time' => (strtotime('13:00:00') - strtotime('12:00:00')) / 60,
                'work_time' => 720,
                'remarks' => '通常勤務',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $attendances[] = [
                'user_id' => 2,
                'date' => $date->format('Y-m-d'),
                'start_time' => '09:30:00',
                'end_time' => '18:30:00',
                'start_break_time' => '12:30:00',
                'end_break_time' => '13:30:00',
                'break_time' => (strtotime('13:30:00') - strtotime('12:30:00')) / 60,
                'work_time' => 720,
                'remarks' => '通常勤務',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        \DB::table('attendances')->insert($attendances);
    }
}
