<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceVoucherRowTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_voucher_row_types')->insert([
            ['id' => 1, 'sort_id' => 10, 'value' => '売上'],
            ['id' => 2, 'sort_id' => 20, 'value' => '返品・キャンセル'],
            ['id' => 3, 'sort_id' => 30, 'value' => '値引'],
            ['id' => 4, 'sort_id' => 40, 'value' => 'メモ'],
            ['id' => 5, 'sort_id' => 50, 'value' => '余白'],
        ]);
    }
}
