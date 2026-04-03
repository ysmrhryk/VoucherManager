<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingCycleTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('billing_cycle_types')->insert([
            ['id' => 1, 'sort_id' => 10, 'value' => '随時'],
            ['id' => 2, 'sort_id' => 20, 'value' => '指定日'],
            ['id' => 3, 'sort_id' => 30, 'value' => '月末']
        ]);
    }
}
