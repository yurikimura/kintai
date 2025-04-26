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
}
