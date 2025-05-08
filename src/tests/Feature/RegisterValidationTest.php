<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前未入力時のバリデーションメッセージをテスト
     *
     * @return void
     */
    public function test_register_validation_error_when_name_is_empty()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /**
     * メールアドレス未入力時のバリデーションメッセージをテスト
     */
    public function test_register_validation_error_when_email_is_empty()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワードが8文字未満の場合のバリデーションメッセージをテスト
     */
    public function test_register_validation_error_when_password_is_too_short()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上にしてください',
        ]);
    }

    /**
     * パスワード確認が一致しない場合のバリデーションメッセージをテスト
     */
    public function test_register_validation_error_when_password_confirmation_does_not_match()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /**
     * パスワード未入力時のバリデーションメッセージをテスト
     */
    public function test_register_validation_error_when_password_is_empty()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * 正常な入力時にユーザーがデータベースに保存されることをテスト
     */
    public function test_register_successfully_creates_user()
    {
        $response = $this->post('/register', [
            'name' => '新規ユーザー',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('attendance.index'));
        $this->assertDatabaseHas('users', [
            'name' => '新規ユーザー',
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * 正常な入力時にユーザー情報がデータベースに正しく保存されることを詳細に検証するテスト
     */
    public function test_register_stores_correct_user_data_in_database()
    {
        $userData = [
            'name' => 'テスト太郎',
            'email' => 'taro.test@example.com',
            'password' => 'secure_password123',
            'password_confirmation' => 'secure_password123',
        ];

        $response = $this->post('/register', $userData);

        // リダイレクトの確認
        $response->assertRedirect(route('attendance.index'));

        // ユーザーが認証されていることを確認
        $this->assertAuthenticated();

        // データベースに正しいデータが保存されていることを確認
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // パスワードがハッシュ化されて保存されていることを確認
        $user = \App\Models\User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertNotEquals($userData['password'], $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($userData['password'], $user->password));
    }
}
