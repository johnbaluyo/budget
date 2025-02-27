<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('divisions')->insert([
            [
                'id' => 1,
                'division_name' => 'Knowledge Management Division',
                'division_acronym' => 'KMD',
                'location' => '15th flr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'division_name' => 'Training Division',
                'division_acronym' => 'TD',
                'location' => '15th flr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'division_name' => 'Research Division',
                'division_acronym' => 'RD',
                'location' => '12th flr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'division_name' => 'Finance and Administrative Division',
                'division_acronym' => 'FAD',
                'location' => '16th flr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'division_name' => 'Office of the Executive Director',
                'division_acronym' => 'OED',
                'location' => '16th flr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
