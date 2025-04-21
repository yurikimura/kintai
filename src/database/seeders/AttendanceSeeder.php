<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // テストユーザーを取得
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('テストユーザーが見つかりません。先にUserSeederを実行してください。');
            return;
        }

        // 過去3ヶ月分のデータを作成
        for ($monthsAgo = 0; $monthsAgo <= 2; $monthsAgo++) {
            $startDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $endDate = Carbon::now()->subMonths($monthsAgo)->endOfMonth();

            foreach ($users as $user) {
                // 各日付に対してテストデータを作成
                for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                    // 土日はスキップ
                    if ($date->isWeekend()) {
                        continue;
                    }

                    // ランダムな出退勤時間を生成
                    $startTime = Carbon::createFromTime(8, rand(30, 59), 0);
                    $endTime = $startTime->copy()->addHours(9);
                    $breakStart = Carbon::createFromTime(12, 0, 0);
                    $breakEnd = $breakStart->copy()->addHours(1);

                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i:s'),
                        'end_time' => $endTime->format('H:i:s'),
                        'start_break_time' => $breakStart->format('H:i:s'),
                        'end_break_time' => $breakEnd->format('H:i:s'),
                        'break_time' => 60,
                        'work_time' => 480,
                        'status' => rand(0, 1) ? 'approved' : 'pending',
                    ]);
                }
            }
        }

        $this->command->info('Attendance table seeded successfully!');
    }
}