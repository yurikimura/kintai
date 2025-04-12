<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('threads')->insert([
            [
                'title' => 'テストスレッド1',
                'content' => 'これはテストスレッド1の内容です。',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'テストスレッド2',
                'content' => 'これはテストスレッド2の内容です。',
                'user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
