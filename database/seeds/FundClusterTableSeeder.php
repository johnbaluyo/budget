<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundClusterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fundclusters')->insert([
            [
                'code' => 'RAF-01',
                'name' => 'General Fund',
            ],

        ]);
    }
}
