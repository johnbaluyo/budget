<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PositionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        DB::table('positions')->insert([
            [
                'id' => 1,
                'position_name' => 'ISA-II',
                'salary_grade' => 16,
                'step_increment' => 1,
                'plantilla_code' => 'ISA-II',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'position_name' => 'CP-II',
                'salary_grade' => 15,
                'step_increment' => 1,
                'plantilla_code' => 'CP-II',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'position_name' => 'ISA-I',
                'salary_grade' => 12,
                'step_increment' => 1,
                'plantilla_code' => 'ISA-I',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
