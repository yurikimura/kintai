<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceDateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * /attendance に表示されている日時が現在の日時と一致しているかをテスト
     */
    public function test_attendance_page_displays_current_date()
    {
        // テスト用ユーザー作成＆ログイン
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $currentDate = Carbon::now()->format('Y年m月d日');
        $response->assertSee($currentDate);
    }

    /**
     * 出勤がまだ押されていない場合、ステータスが「勤務外」と表示されているかをテスト
     */
    public function test_attendance_status_is_out_of_work_before_clock_in()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    /**
     * 出勤中で休憩中でない場合、ステータスが「出勤中」と表示されているかをテスト
     */
    public function test_attendance_status_is_working_when_clocked_in_and_not_on_break()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 出勤レコードを作成（休憩開始・終了はnull）
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'start_break_time' => null,
            'end_break_time' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }
}
