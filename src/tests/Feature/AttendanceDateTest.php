<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;

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
        $currentDate = $now->format('Y年n月j日') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$now->dayOfWeek] . ')';
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
            'working_status' => 'working',
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
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(8)->format('H:i:s'),
            'end_time' => now()->format('H:i:s'),
            'start_break_time' => null,
            'end_break_time' => null,
            'working_status' => 'off'
        ]);

        $response = $this->get('/attendance');

        $response->assertDontSee('<button class="attendance-button" id="startWork">出勤</button>', false);
        $response->assertSee('class="attendance-button hidden" id="startWork"', false);
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

        // 退勤ボタンを押す
        $response = $this->post('/attendance/end');
        $response->assertStatus(200)
            ->assertJson([
                'message' => '退勤を記録しました'
            ]);

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

        // 出勤ボタンを押す
        $response = $this->post('/attendance');
        $response->assertStatus(200)
            ->assertJson([
                'message' => '出勤を記録しました'
            ]);

        // 出勤レコードを取得
        $attendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('date', now()->format('Y-m-d'))
            ->first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->start_time);
        $this->assertNull($attendance->end_time);

        // 退勤ボタンを押す
        $response = $this->post('/attendance/end');
        $response->assertStatus(200)
            ->assertJson([
                'message' => '退勤を記録しました'
            ]);

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

        $previousMonth = Carbon::now()->subMonth();
        $response = $this->get('/attendance/list?date=' . $previousMonth->format('Y-m'));

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

        $nextMonth = Carbon::now()->addMonth();
        $response = $this->get('/attendance/list?date=' . $nextMonth->format('Y-m'));

        $expected = $nextMonth->format('Y年m月');
        $response->assertSee($expected);
    }

    /**
     * 勤怠一覧ページで「詳細」ボタンをクリックすると、勤怠詳細ページに遷移することをテスト
     */
    public function test_attendance_list_detail_button_redirects_to_attendance_detail_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(8)->format('H:i:s'),
            'end_time' => now()->format('H:i:s'),
            'start_break_time' => now()->subHours(4)->format('H:i:s'),
            'end_break_time' => now()->subHours(3)->format('H:i:s'),
        ]);

        // 勤怠一覧ページにアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 詳細ページへのリンクが存在することを確認
        $response->assertSee('詳細');
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        // 詳細ページにアクセス
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        // 詳細ページに勤怠情報が表示されていることを確認
        $detailResponse->assertSee($attendance->date->format('Y年m月d日'));
        $detailResponse->assertSee($attendance->start_time->format('H:i'));
        $detailResponse->assertSee($attendance->end_time->format('H:i'));
    }

    /**
     * 勤怠一覧ページで「詳細」ボタンを押すと、詳細ページにログインユーザーの名前が表示されることをテスト
     */
    public function test_attendance_detail_page_displays_logged_in_user_name()
    {
        // テスト用ユーザーを作成（名前を指定）
        $user = User::factory()->create([
            'name' => 'テスト太郎'
        ]);
        $this->actingAs($user);

        // 勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->subHours(8)->format('H:i:s'),
            'end_time' => now()->format('H:i:s'),
        ]);

        // 勤怠一覧ページにアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 詳細ページへのリンクURLを取得
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);

        // 詳細ページにアクセス
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        // 詳細ページにユーザー名が表示されていることを確認
        $detailResponse->assertSee('テスト太郎');
    }

    /**
     * 勤怠一覧ページで「詳細」ボタンを押すと、詳細ページに選択した日付の勤怠情報が表示されることをテスト
     */
    public function test_attendance_detail_page_displays_selected_date()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 特定の日付の勤怠記録を作成
        $specificDate = Carbon::create(2023, 5, 15);
        Carbon::setTestNow($specificDate);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $specificDate->format('Y-m-d'),
            'start_time' => $specificDate->copy()->setHour(9)->setMinute(0)->setSecond(0)->format('H:i:s'),
            'end_time' => $specificDate->copy()->setHour(18)->setMinute(0)->setSecond(0)->format('H:i:s'),
        ]);

        // 勤怠一覧ページにアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 詳細ページへのリンクURLを取得
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);

        // 詳細ページにアクセス
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        // 詳細ページに選択した日付が表示されていることを確認
        $expectedDate = $specificDate->format('Y年m月d日');
        $detailResponse->assertSee($expectedDate);

        // 開始・終了時間も確認
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');

        // テスト後、固定した時刻を元に戻す
        Carbon::setTestNow(null);
    }

    /**
     * 勤怠一覧ページで「詳細」ボタンを押すと、詳細ページに表示される出勤・退勤時間が打刻情報と一致することをテスト
     */
    public function test_attendance_detail_page_displays_correct_punch_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 特定の打刻時間を持つ勤怠記録を作成
        $startTime = '09:30:00';
        $endTime = '18:45:00';
        $startBreakTime = '12:00:00';
        $endBreakTime = '13:00:00';

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_break_time' => $startBreakTime,
            'end_break_time' => $endBreakTime,
        ]);

        // 勤怠一覧ページにアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        // 詳細ページに表示される時間が打刻時間と一致することを確認
        $formattedStartTime = Carbon::parse($startTime)->format('H:i');
        $formattedEndTime = Carbon::parse($endTime)->format('H:i');

        // 出勤・退勤時間の確認
        $detailResponse->assertSee($formattedStartTime);
        $detailResponse->assertSee($formattedEndTime);

        // 休憩時間の確認（休憩時間が表示される場合）
        $formattedStartBreakTime = Carbon::parse($startBreakTime)->format('H:i');
        $formattedEndBreakTime = Carbon::parse($endBreakTime)->format('H:i');
        $detailResponse->assertSee($formattedStartBreakTime);
        $detailResponse->assertSee($formattedEndBreakTime);
    }

    /**
     * 勤怠一覧ページで「詳細」ボタンを押すと、詳細ページに表示される休憩時間が打刻情報と一致することをテスト
     */
    public function test_attendance_detail_page_displays_correct_break_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 休憩時間を特定の値に設定した勤怠記録を作成
        $startBreakTime = '12:15:00';
        $endBreakTime = '13:30:00';
        $breakTimeMinutes = 75; // 1時間15分 = 75分

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'start_break_time' => $startBreakTime,
            'end_break_time' => $endBreakTime,
            'break_time' => $breakTimeMinutes,
        ]);

        // 勤怠一覧ページにアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 一覧ページに休憩時間（時:分形式）が表示されていることを確認
        $formattedBreakTime = floor($breakTimeMinutes / 60) . ':' . str_pad($breakTimeMinutes % 60, 2, '0', STR_PAD_LEFT);
        $response->assertSee($formattedBreakTime);

        // 詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        // 詳細ページに休憩開始・終了時間が表示されていることを確認
        $formattedStartBreakTime = Carbon::parse($startBreakTime)->format('H:i');
        $formattedEndBreakTime = Carbon::parse($endBreakTime)->format('H:i');

        $detailResponse->assertSee($formattedStartBreakTime);
        $detailResponse->assertSee($formattedEndBreakTime);

        // 詳細ページに表示される休憩時間が登録した休憩時間と一致することを確認
        $detailResponse->assertSee($formattedBreakTime);

        // 休憩時間の計算が正しいことを確認
        $calculatedBreakTime = Carbon::parse($startBreakTime)->diffInMinutes(Carbon::parse($endBreakTime));
        $this->assertEquals($breakTimeMinutes, $calculatedBreakTime);
    }

    /**
     * 勤怠詳細ページで出勤時間を退勤時間より後に設定すると、バリデーションエラーメッセージが表示されることをテスト
     */
    public function test_attendance_detail_validates_start_time_must_be_before_end_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 通常の勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        // 出勤時間を退勤時間より後に設定してフォームを送信
        $invalidData = [
            'start_time' => '19:00', // 退勤時間より後
            'end_time' => '18:00',
            '_method' => 'PUT'
        ];

        // フォーム送信
        $updateResponse = $this->post(route('attendance.update', $attendance->id), $invalidData);

        // リダイレクト後にエラーメッセージが含まれていることを確認
        $updateResponse->assertSessionHasErrors('start_time');
        $updateResponse->assertSessionHasErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です']);

        // リダイレクト先でバリデーションメッセージが表示されることを確認
        $this->followRedirects($updateResponse)
            ->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /**
     * 勤怠詳細ページで休憩開始時間を退勤時間より後に設定すると、バリデーションエラーメッセージが表示されることをテスト
     */
    public function test_attendance_detail_validates_start_break_time_must_be_before_end_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 通常の勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'start_break_time' => '12:00:00',
            'end_break_time' => '13:00:00',
        ]);

        // 詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        // 休憩開始時間を退勤時間より後に設定してフォームを送信
        $invalidData = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'start_break_time' => '19:00', // 退勤時間より後
            'end_break_time' => '20:00',
            '_method' => 'PUT'
        ];

        // フォーム送信
        $updateResponse = $this->post(route('attendance.update', $attendance->id), $invalidData);

        // リダイレクト後にエラーメッセージが含まれていることを確認
        $updateResponse->assertSessionHasErrors('start_break_time');
        $updateResponse->assertSessionHasErrors(['start_break_time' => '出勤時間もしくは退勤時間が不適切な値です']);

        // リダイレクト先でバリデーションメッセージが表示されることを確認
        $this->followRedirects($updateResponse)
            ->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /**
     * 勤怠詳細ページで休憩終了時間を退勤時間より後に設定すると、バリデーションエラーメッセージが表示されることをテスト
     */
    public function test_attendance_detail_validates_end_break_time_must_be_before_end_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 通常の勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'start_break_time' => '12:00:00',
            'end_break_time' => '13:00:00',
        ]);

        // 詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        // 休憩終了時間を退勤時間より後に設定してフォームを送信
        $invalidData = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'start_break_time' => '12:00',
            'end_break_time' => '19:00', // 退勤時間より後
            '_method' => 'PUT'
        ];

        // フォーム送信
        $updateResponse = $this->post(route('attendance.update', $attendance->id), $invalidData);

        // リダイレクト後にエラーメッセージが含まれていることを確認
        $updateResponse->assertSessionHasErrors('end_break_time');
        $updateResponse->assertSessionHasErrors(['end_break_time' => '出勤時間もしくは退勤時間が不適切な値です']);

        // リダイレクト先でバリデーションメッセージが表示されることを確認
        $this->followRedirects($updateResponse)
            ->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /**
     * 勤怠詳細ページで備考欄を未入力のまま保存すると、バリデーションエラーメッセージが表示されることをテスト
     */
    public function test_attendance_detail_validates_remarks_is_required()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 通常の勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'remarks' => '通常勤務'
        ]);

        // 詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        // 備考欄を空にしてフォームを送信
        $invalidData = [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '', // 備考欄が空
            '_method' => 'PUT'
        ];

        // フォーム送信
        $updateResponse = $this->post(route('attendance.update', $attendance->id), $invalidData);

        // リダイレクト後にエラーメッセージが含まれていることを確認
        $updateResponse->assertSessionHasErrors('remarks');
        $updateResponse->assertSessionHasErrors(['remarks' => '備考を記入してください']);

        // リダイレクト先でバリデーションメッセージが表示されることを確認
        $this->followRedirects($updateResponse)
            ->assertSee('備考を記入してください');
    }

    /**
     * 勤怠情報を修正して保存すると、管理者画面の申請一覧と承認画面に表示されることをテスト
     */
    public function test_modified_attendance_appears_in_admin_approval_and_request_list()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト一般ユーザー'
        ]);

        // 管理者ユーザーを作成
        $admin = \App\Models\Admin::factory()->create();

        // 一般ユーザーとしてログイン
        $this->actingAs($user);

        // 勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => 'pending',
            'remarks' => '通常勤務'
        ]);

        // 勤怠詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        // 勤怠情報を修正して保存
        $updatedData = [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正後の勤務内容',
            '_method' => 'PUT'
        ];

        $updateResponse = $this->post(route('attendance.update', $attendance->id), $updatedData);
        $updateResponse->assertRedirect(route('attendance.show', $attendance->id));

        // 申請が作成されていることを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        // 勤怠情報が更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'remarks' => '修正後の勤務内容'
        ]);

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 管理者の申請一覧ページにアクセス
        $adminRequestListResponse = $this->get(route('admin.stamp-correction-requests.index'));
        $adminRequestListResponse->assertStatus(200);

        // 申請一覧ページに修正した勤怠情報が表示されていることを確認
        $adminRequestListResponse->assertSee('テスト一般ユーザー');
        $adminRequestListResponse->assertSee('修正後の勤務内容');
        $adminRequestListResponse->assertSee('承認待ち');

        // 管理者の勤怠詳細ページにアクセス
        $adminDetailUrl = route('admin.attendance.show', ['id' => $attendance->id]);
        $adminDetailResponse = $this->get($adminDetailUrl);
        $adminDetailResponse->assertStatus(200);

        // 勤怠詳細ページに修正した情報が表示されていることを確認
        $adminDetailResponse->assertSee('テスト一般ユーザー');
        $adminDetailResponse->assertSee('修正後の勤務内容');
        $adminDetailResponse->assertSee('10:00');
        $adminDetailResponse->assertSee('19:00');

        // 承認ボタンが表示されていることを確認
        $adminDetailResponse->assertSee('承認');
    }

    /**
     * 勤怠詳細を修正して保存すると、申請一覧に自分の申請が表示されることをテスト
     */
    public function test_modified_attendance_appears_in_user_request_list()
    {
        // ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー'
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 複数の勤怠記録を作成して修正
        $firstAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDays(2)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => 'pending',
            'remarks' => '通常勤務1'
        ]);

        $secondAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDays(1)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => 'pending',
            'remarks' => '通常勤務2'
        ]);

        $thirdAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => 'pending',
            'remarks' => '通常勤務3'
        ]);

        // 1つ目の勤怠の詳細ページにアクセスして修正
        $detailUrl1 = route('attendance.show', ['id' => $firstAttendance->id]);
        $this->get($detailUrl1);

        $updatedData1 = [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正後の勤務内容1',
            '_method' => 'PUT'
        ];

        $this->post(route('attendance.update', $firstAttendance->id), $updatedData1);

        // 2つ目の勤怠の詳細ページにアクセスして修正
        $detailUrl2 = route('attendance.show', ['id' => $secondAttendance->id]);
        $this->get($detailUrl2);

        $updatedData2 = [
            'start_time' => '08:30',
            'end_time' => '17:30',
            'remarks' => '修正後の勤務内容2',
            '_method' => 'PUT'
        ];

        $this->post(route('attendance.update', $secondAttendance->id), $updatedData2);

        // 3つ目の勤怠の詳細ページにアクセスして修正
        $detailUrl3 = route('attendance.show', ['id' => $thirdAttendance->id]);
        $this->get($detailUrl3);

        $updatedData3 = [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '修正後の勤務内容3',
            '_method' => 'PUT'
        ];

        $this->post(route('attendance.update', $thirdAttendance->id), $updatedData3);

        // 申請一覧ページにアクセス
        $requestListResponse = $this->get(route('stamp_correction_request.list'));
        $requestListResponse->assertStatus(200);

        // 申請一覧ページに全ての修正した勤怠情報が表示されていることを確認
        $requestListResponse->assertSee('修正後の勤務内容1');
        $requestListResponse->assertSee('修正後の勤務内容2');
        $requestListResponse->assertSee('修正後の勤務内容3');
        $requestListResponse->assertSee('承認待ち');
        $requestListResponse->assertSee(now()->subDays(2)->format('Y/m/d'));
        $requestListResponse->assertSee(now()->subDays(1)->format('Y/m/d'));
        $requestListResponse->assertSee(now()->format('Y/m/d'));
        $requestListResponse->assertSee('テストユーザー');

        // 各勤怠詳細へのリンクが含まれていることを確認
        $requestListResponse->assertSee(route('attendance.show', ['id' => $firstAttendance->id]));
        $requestListResponse->assertSee(route('attendance.show', ['id' => $secondAttendance->id]));
        $requestListResponse->assertSee(route('attendance.show', ['id' => $thirdAttendance->id]));

        // 異なるユーザーの申請は表示されないことを確認
        $anotherUser = User::factory()->create();
        $anotherAttendance = Attendance::factory()->create([
            'user_id' => $anotherUser->id,
            'date' => now()->format('Y-m-d'),
            'remarks' => '別ユーザーの勤務'
        ]);

        // 他のユーザーの勤怠情報が表示されていないことを確認
        $requestListResponse->assertDontSee('別ユーザーの勤務');
    }

    /**
     * 勤怠詳細を修正して保存後、承認一覧画面から詳細ボタンを押すと申請詳細画面に遷移することをテスト
     */
    public function test_request_list_detail_button_redirects_to_request_detail_page()
    {
        // ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー'
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 勤怠記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => 'pending',
            'remarks' => '通常勤務'
        ]);

        // 勤怠詳細ページにアクセス
        $detailUrl = route('attendance.show', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);
        $response->assertStatus(200);

        // 勤怠情報を修正して保存
        $updatedData = [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'remarks' => '修正後の勤務内容',
            '_method' => 'PUT'
        ];

        $updateResponse = $this->post(route('attendance.update', $attendance->id), $updatedData);
        $updateResponse->assertRedirect(route('attendance.show', $attendance->id));

        // 申請が作成されていることを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        // 申請一覧ページにアクセス
        $requestListResponse = $this->get(route('stamp_correction_request.list'));
        $requestListResponse->assertStatus(200);

        // 申請一覧ページに修正した勤怠情報が表示されていることを確認
        $requestListResponse->assertSee('テストユーザー');
        $requestListResponse->assertSee('修正後の勤務内容');
        $requestListResponse->assertSee('承認待ち');

        // 詳細ページへのリンクが存在することを確認
        $requestListResponse->assertSee(route('attendance.show', ['id' => $attendance->id]));

        // 詳細ページにアクセス
        $detailResponse = $this->get(route('attendance.show', ['id' => $attendance->id]));
        $detailResponse->assertStatus(200);

        // 詳細ページに修正した情報が表示されていることを確認
        $detailResponse->assertSee('テストユーザー');
        $detailResponse->assertSee('修正後の勤務内容');
        $detailResponse->assertSee('10:00');
        $detailResponse->assertSee('19:00');

        // 現在のステータスが「承認待ち」であることを確認（申請中の状態）
        $attendance->refresh();
        $this->assertEquals('pending', $attendance->status);
    }
}
