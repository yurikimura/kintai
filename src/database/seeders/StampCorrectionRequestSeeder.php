<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class StampCorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 過去3ヶ月分の勤怠データを取得
        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $attendances = Attendance::whereBetween('date', [$startDate, $endDate])
            ->get();

        if ($attendances->isEmpty()) {
            $this->command->info('修正対象の勤怠データが見つかりません。');
            return;
        }

        // 各勤怠データに対して修正申請を作成
        foreach ($attendances as $attendance) {
            \DB::table('stamp_correction_requests')->insert([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Stamp correction requests seeded successfully!');
    }
}
