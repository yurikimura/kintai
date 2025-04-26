<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレス未入力時のバリデーションメッセージをテスト
     */
    public function test_login_validation_error_when_email_is_empty()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワード未入力時のバリデーションメッセージをテスト
     */
    public function test_login_validation_error_when_password_is_empty()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * 入力内容が登録内容と一致しない場合のエラーメッセージをテスト
     */
    public function test_login_error_when_credentials_do_not_match()
    {
        // 事前にユーザーを作成
        \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $response->assertRedirect('/login');
    }
}
