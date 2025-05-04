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

        // テスト実行時の時刻をCarbonで固定
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $response = $this->get('/attendance');

        // 日付の確認
        $currentDate = $now->format('Y年m月d日');
        $response->assertSee($currentDate);

        // 時間と分の確認
        $currentHour = $now->format('H');
        $currentMinute = $now->format('i');
        $response->assertSee($currentHour);
        $response->assertSee($currentMinute);

        // テスト後、固定した時刻を元に戻す
        Carbon::setTestNow(null);
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

    /**
     * 休憩ボタンが押されている場合、ステータスが「休憩中」と表示されているかをテスト
     */
    public function test_attendance_status_is_on_break_when_break_started()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 出勤レコードを作成（休憩開始のみセット）
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'start_break_time' => now()->format('H:i:s'),
            'end_break_time' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    /**
     * 退勤ボタンが押されている場合、ステータスが「退勤済」と表示されているかをテスト
     */
    public function test_attendance_status_is_left_work_when_clocked_out()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 出勤レコードを作成（退勤時刻をセット）
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(8)->format('H:i:s'),
            'end_time' => now()->format('H:i:s'),
            'start_break_time' => null,
            'end_break_time' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }

    /**
     * 勤務外ステータスのユーザーでログインした際に「出勤」ボタンが表示されるかをテスト
     */
    public function test_attendance_page_shows_clock_in_button_when_status_is_out_of_work()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤');
    }

    /**
     * 退勤済ステータスのユーザーでログインした際に「出勤」ボタンが表示されないかをテスト
     */
    public function test_attendance_page_does_not_show_clock_in_button_when_status_is_left_work()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 退勤済の出勤レコードを作成
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(8)->format('H:i:s'),
            'end_time' => now()->format('H:i:s'),
            'start_break_time' => null,
            'end_break_time' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    /**
     * 勤務中ユーザーでログインした際に「退勤」ボタンが表示され、押すとステータスが「退勤済」になるかをテスト
     */
    public function test_attendance_page_shows_clock_out_button_and_status_changes_to_left_work()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 勤務中の出勤レコードを作成（退勤・休憩なし）
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'start_break_time' => null,
            'end_break_time' => null,
        ]);

        // 画面に「退勤」ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        // 退勤ボタンを押す（POSTリクエストを想定。ルートやパラメータは実装に合わせて調整してください）
        $response = $this->post('/attendance/clock-out', [
            'attendance_id' => $attendance->id,
        ]);
        $response->assertRedirect('/attendance');

        // 再度画面を取得し、ステータスが「退勤済」になっていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /**
     * 勤務外ユーザーが出勤・退勤した際、退勤時刻がDBに正確に記録されていることをテスト
     */
    public function test_clock_in_and_clock_out_records_correct_end_time_in_database()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 出勤ボタンを押す（POSTリクエストを想定）
        $response = $this->post('/attendance/clock-in');
        $response->assertRedirect('/attendance');

        // 出勤レコードを取得
        $attendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('date', now()->format('Y-m-d'))
            ->first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->start_time);
        $this->assertNull($attendance->end_time);

        // 退勤ボタンを押す（POSTリクエストを想定）
        $response = $this->post('/attendance/clock-out', [
            'attendance_id' => $attendance->id,
        ]);
        $response->assertRedirect('/attendance');

        // DBの退勤時刻が現在時刻とほぼ一致していることを確認
        $attendance->refresh();
        $this->assertNotNull($attendance->end_time);
        $this->assertEquals(
            now()->format('H:i'),
            Carbon::parse($attendance->end_time)->format('H:i')
        );
    }

    /**
     * 勤怠一覧ページで現在の月が表示されていることをテスト
     */
    public function test_attendance_list_page_displays_current_month()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $currentMonth = Carbon::now()->format('Y年m月');
        $response->assertSee($currentMonth);
    }

    /**
     * 勤怠一覧ページで「前月」ボタンを押すと前月が表示されることをテスト
     */
    public function test_attendance_list_page_displays_previous_month_when_prev_button_clicked()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 「前月」ボタン押下を想定し、クエリパラメータで前月を指定
        $previousMonth = Carbon::now()->subMonth();
        $response = $this->get('/attendance/list?month=' . $previousMonth->format('Y-m'));

        $expected = $previousMonth->format('Y年m月');
        $response->assertSee($expected);
    }

    /**
     * 勤怠一覧ページで「翌月」ボタンを押すと翌月が表示されることをテスト
     */
    public function test_attendance_list_page_displays_next_month_when_next_button_clicked()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 「翌月」ボタン押下を想定し、クエリパラメータで翌月を指定
        $nextMonth = Carbon::now()->addMonth();
        $response = $this->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $expected = $nextMonth->format('Y年m月');
        $response->assertSee($expected);
    }
}
