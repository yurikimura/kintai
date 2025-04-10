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
            'email' => 'admin@example.com',
            'password' => \Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
