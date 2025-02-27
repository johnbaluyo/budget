<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin Rufina',
                'email' => 'fina.arroyo@psrti.gov.ph',
                'position_id' => '3',
                'division_id' => '1',
                'user_type' => '0',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin John',
                'email' => 'janny@admin.com',
                'position_id' => '2',
                'division_id' => '1',
                'user_type' => '0',
                'password' => Hash::make('qweqweqwe'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
          
        ]);
    }
}
