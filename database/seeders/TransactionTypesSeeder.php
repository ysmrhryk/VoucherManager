<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transaction_types')->insert([
            ['id' => 1, 'sort_id' => 10, 'value' => '掛売（請求書）'],
            ['id' => 2, 'sort_id' => 20, 'value' => '都度（現金）']
        ]);
    }
}
