<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('admin_users')->insert([
            'name' => 'Admin',
            'email' => 'admintest@example.com',
            'password' => \Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
