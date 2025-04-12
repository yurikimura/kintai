<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BreakTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('break_times')->insert([
            [
                'attendance_id' => 1,
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 2,
                'start_time' => '12:30:00',
                'end_time' => '13:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
