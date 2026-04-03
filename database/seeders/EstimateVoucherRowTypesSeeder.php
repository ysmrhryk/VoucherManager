<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstimateVoucherRowTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estimate_voucher_row_types')->insert([
            ['id' => 1, 'sort_id' => 10, 'value' => '見積'],
            ['id' => 2, 'sort_id' => 20, 'value' => '値引'],
            ['id' => 3, 'sort_id' => 30, 'value' => 'メモ'],
            ['id' => 4, 'sort_id' => 40, 'value' => '余白'],
        ]);
    }
}
