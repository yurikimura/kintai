<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StampCorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('stamp_correction_requests')->insert([
            [
                'user_id' => 1,
                'attendance_id' => 1,
                'requested_start_time' => '09:00:00',
                'requested_end_time' => '18:00:00',
                'request_type' => 'start_end',
                'request_time' => '2025-04-11 19:00:00',
                'current_time' => '19:00:00',
                'reason' => '打刻忘れ',
                'status' => 'pending',
                'request_date' => '2025-04-11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'attendance_id' => 2,
                'requested_start_time' => '09:30:00',
                'requested_end_time' => '18:30:00',
                'request_type' => 'start_end',
                'request_time' => '2025-04-11 19:00:00',
                'current_time' => '19:00:00',
                'reason' => '打刻ミス',
                'status' => 'pending',
                'request_date' => '2025-04-11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
