<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーがログインして勤怠詳細ページを表示するテスト
     */
    public function test_admin_can_login_and_view_attendance_details()
    {
        // 管理者ユーザーを作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now()->subHours(8)->format('H:i:s'),
            'end_time' => Carbon::now()->format('H:i:s'),
            'break_time' => 60, // 1時間の休憩
            'work_time' => 420, // 7時間勤務 (8時間 - 1時間休憩)
            'remarks' => 'テスト備考',
        ]);

        // 管理者ログイン画面を表示
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('ログイン');

        // 管理者としてログイン
        $loginResponse = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/admin/attendance/list');

        // セッションに管理者認証情報があることを確認
        $this->assertAuthenticated('admin');

        // 勤怠詳細ページにアクセス
        $detailResponse = $this->get('/admin/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);

        // 勤怠詳細ページに正しい情報が表示されていることを確認
        $detailResponse->assertSee($user->name); // ユーザー名
        $detailResponse->assertSee(Carbon::parse($attendance->date)->format('Y年m月d日')); // 日付
        $detailResponse->assertSee(Carbon::parse($attendance->start_time)->format('H:i')); // 開始時間
        $detailResponse->assertSee(Carbon::parse($attendance->end_time)->format('H:i')); // 終了時間
        $detailResponse->assertSee($attendance->remarks); // 備考
    }

    /**
     * 管理者ユーザーがログインして勤怠一覧画面でその日の全ユーザーの勤怠情報を確認するテスト
     */
    public function test_admin_can_view_all_users_attendance_for_the_day()
    {
        // テスト日時を固定
        $testDate = Carbon::create(2023, 5, 15, 12, 0, 0);
        Carbon::setTestNow($testDate);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 複数の一般ユーザーを作成
        $users = [
            User::factory()->create(['name' => '山田太郎']),
            User::factory()->create(['name' => '鈴木花子']),
            User::factory()->create(['name' => '佐藤次郎'])
        ];

        // 各ユーザーの勤怠データを作成
        $attendances = [];

        // 山田太郎の勤怠（通常勤務）
        $attendances[] = Attendance::factory()->create([
            'user_id' => $users[0]->id,
            'date' => $testDate->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'break_time' => 60, // 1時間の休憩
            'work_time' => 480, // 8時間勤務
            'remarks' => '通常勤務',
        ]);

        // 鈴木花子の勤怠（短時間勤務）
        $attendances[] = Attendance::factory()->create([
            'user_id' => $users[1]->id,
            'date' => $testDate->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '16:00:00',
            'break_time' => 30, // 30分の休憩
            'work_time' => 330, // 5時間30分勤務
            'remarks' => '短時間勤務',
        ]);

        // 佐藤次郎の勤怠（残業あり）
        $attendances[] = Attendance::factory()->create([
            'user_id' => $users[2]->id,
            'date' => $testDate->format('Y-m-d'),
            'start_time' => '08:30:00',
            'end_time' => '19:30:00',
            'break_time' => 90, // 1時間30分の休憩
            'work_time' => 630, // 10時間30分勤務
            'remarks' => '残業あり',
        ]);

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス（デフォルトで当日の日付）
        $listResponse = $this->get('/admin/attendance/list');
        $listResponse->assertStatus(200);

        // 勤怠一覧ページに日付が表示されていることを確認
        $formattedDate = $testDate->format('Y年n月j日');
        $listResponse->assertSee($formattedDate);

        // 各ユーザーの勤怠情報が表示されていることを確認
        foreach ($users as $index => $user) {
            $attendance = $attendances[$index];

            // ユーザー名の確認
            $listResponse->assertSee($user->name);

            // 勤怠時間の確認
            $startTime = Carbon::parse($attendance->start_time)->format('H:i');
            $endTime = Carbon::parse($attendance->end_time)->format('H:i');
            $listResponse->assertSee($startTime);
            $listResponse->assertSee($endTime);

            // 休憩時間の確認（分を時:分形式に変換）
            $breakHours = floor($attendance->break_time / 60);
            $breakMinutes = $attendance->break_time % 60;
            $formattedBreakTime = sprintf('%d:%02d', $breakHours, $breakMinutes);
            $listResponse->assertSee($formattedBreakTime);

            // 合計勤務時間の確認（分を時:分形式に変換）
            $workHours = floor($attendance->work_time / 60);
            $workMinutes = $attendance->work_time % 60;
            $formattedWorkTime = sprintf('%d:%02d', $workHours, $workMinutes);
            $listResponse->assertSee($formattedWorkTime);

            // 詳細リンクの確認
            $detailUrl = route('admin.attendance.show', ['id' => $attendance->id]);
            $listResponse->assertSee($detailUrl);
        }

        // テスト日時をリセット
        Carbon::setTestNow(null);
    }

    /**
     * 管理者ユーザーがログインして勤怠一覧画面にその日の日付が表示されることを確認するテスト
     */
    public function test_admin_attendance_list_displays_current_date()
    {
        // テスト日時を固定
        $testDate = Carbon::create(2023, 6, 20, 14, 30, 0);
        Carbon::setTestNow($testDate);

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 勤怠一覧ページに日付が表示されていることを確認（複数のフォーマットをチェック）
        $formattedDate1 = $testDate->format('Y年n月j日');
        $formattedDate2 = $testDate->format('Y/m/d');

        // いずれかのフォーマットで日付が表示されていればOK
        $dateFound = false;
        if(strpos($response->getContent(), $formattedDate1) !== false) {
            $dateFound = true;
        } else if(strpos($response->getContent(), $formattedDate2) !== false) {
            $dateFound = true;
        }

        $this->assertTrue($dateFound, '勤怠一覧画面に日付が表示されていません');

        // 前日・翌日へのリンクが存在することを確認
        $prevDay = $testDate->copy()->subDay()->format('Y-m-d');
        $nextDay = $testDate->copy()->addDay()->format('Y-m-d');

        $response->assertSee($prevDay);
        $response->assertSee($nextDay);

        // テスト日時をリセット
        Carbon::setTestNow(null);
    }

    /**
     * 管理者ユーザーが勤怠一覧画面で「前日」ボタンを押すと前日の勤怠情報が表示されることを確認するテスト
     */
    public function test_admin_can_view_previous_day_attendance()
    {
        // テスト日時を固定
        $currentDate = Carbon::create(2023, 7, 15, 10, 0, 0);
        Carbon::setTestNow($currentDate);

        // 前日の日付
        $previousDate = $currentDate->copy()->subDay();

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 一般ユーザーを作成
        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '鈴木花子']);

        // 当日と前日の勤怠データを作成
        // 当日の勤怠
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $currentDate->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '当日の勤務：山田',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $currentDate->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '当日の勤務：鈴木',
        ]);

        // 前日の勤怠
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $previousDate->format('Y-m-d'),
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '前日の勤務：山田',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $previousDate->format('Y-m-d'),
            'start_time' => '09:30:00',
            'end_time' => '18:30:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '前日の勤務：鈴木',
        ]);

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス（現在日付）
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 当日の情報が表示されていることを確認
        $response->assertSee($currentDate->format('Y年n月j日'));
        $response->assertSee('当日の勤務：山田');
        $response->assertSee('当日の勤務：鈴木');
        $response->assertDontSee('前日の勤務：山田');
        $response->assertDontSee('前日の勤務：鈴木');

        // 前日ボタンのリンクURLを取得
        $previousDayUrl = '/admin/attendance/list?date=' . $previousDate->format('Y-m-d');
        $response->assertSee($previousDayUrl);

        // 前日ボタンをクリック（前日の日付でページにアクセス）
        $previousDayResponse = $this->get($previousDayUrl);
        $previousDayResponse->assertStatus(200);

        // 前日の情報が表示されていることを確認
        $previousDayResponse->assertSee($previousDate->format('Y年n月j日'));
        $previousDayResponse->assertSee('前日の勤務：山田');
        $previousDayResponse->assertSee('前日の勤務：鈴木');
        $previousDayResponse->assertDontSee('当日の勤務：山田');
        $previousDayResponse->assertDontSee('当日の勤務：鈴木');

        // 出勤・退勤時間が正しく表示されていることを確認
        $previousDayResponse->assertSee('08:30');
        $previousDayResponse->assertSee('17:30');
        $previousDayResponse->assertSee('09:30');
        $previousDayResponse->assertSee('18:30');

        // テスト日時をリセット
        Carbon::setTestNow(null);
    }

    /**
     * 管理者ユーザーが勤怠一覧画面で「翌日」ボタンを押すと翌日の勤怠情報が表示されることを確認するテスト
     */
    public function test_admin_can_view_next_day_attendance()
    {
        // テスト日時を固定
        $currentDate = Carbon::create(2023, 8, 10, 10, 0, 0);
        Carbon::setTestNow($currentDate);

        // 翌日の日付
        $nextDate = $currentDate->copy()->addDay();

        // 管理者ユーザーを作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 一般ユーザーを作成
        $user1 = User::factory()->create(['name' => '田中一郎']);
        $user2 = User::factory()->create(['name' => '佐藤二郎']);

        // 当日と翌日の勤怠データを作成
        // 当日の勤怠
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $currentDate->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '当日の勤務：田中',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $currentDate->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '当日の勤務：佐藤',
        ]);

        // 翌日の勤怠
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $nextDate->format('Y-m-d'),
            'start_time' => '08:45:00',
            'end_time' => '17:45:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '翌日の勤務：田中',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $nextDate->format('Y-m-d'),
            'start_time' => '09:45:00',
            'end_time' => '18:45:00',
            'break_time' => 60,
            'work_time' => 480,
            'remarks' => '翌日の勤務：佐藤',
        ]);

        // 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 勤怠一覧ページにアクセス（現在日付）
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 当日の情報が表示されていることを確認
        $response->assertSee($currentDate->format('Y年n月j日'));
        $response->assertSee('当日の勤務：田中');
        $response->assertSee('当日の勤務：佐藤');
        $response->assertDontSee('翌日の勤務：田中');
        $response->assertDontSee('翌日の勤務：佐藤');

        // 当日の出勤・退勤時間が表示されていることを確認
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        // 翌日ボタンのリンクURLを取得
        $nextDayUrl = '/admin/attendance/list?date=' . $nextDate->format('Y-m-d');
        $response->assertSee($nextDayUrl);

        // 翌日ボタンをクリック（翌日の日付でページにアクセス）
        $nextDayResponse = $this->get($nextDayUrl);
        $nextDayResponse->assertStatus(200);

        // 翌日の情報が表示されていることを確認
        $nextDayResponse->assertSee($nextDate->format('Y年n月j日'));
        $nextDayResponse->assertSee('翌日の勤務：田中');
        $nextDayResponse->assertSee('翌日の勤務：佐藤');
        $nextDayResponse->assertDontSee('当日の勤務：田中');
        $nextDayResponse->assertDontSee('当日の勤務：佐藤');

        // 翌日の出勤・退勤時間が正しく表示されていることを確認
        $nextDayResponse->assertSee('08:45');
        $nextDayResponse->assertSee('17:45');
        $nextDayResponse->assertSee('09:45');
        $nextDayResponse->assertSee('18:45');

        // さらに翌日・前日への移動ボタンが存在することを確認
        $nextNextDayUrl = '/admin/attendance/list?date=' . $nextDate->copy()->addDay()->format('Y-m-d');
        $prevFromNextDayUrl = '/admin/attendance/list?date=' . $currentDate->format('Y-m-d');

        $nextDayResponse->assertSee($nextNextDayUrl);
        $nextDayResponse->assertSee($prevFromNextDayUrl);

        // テスト日時をリセット
        Carbon::setTestNow(null);
    }
}
