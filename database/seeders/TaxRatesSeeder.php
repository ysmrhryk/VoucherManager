<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tax_rates')->insert([
            ['id' => 1, 'sort_id' => 10, 'name' => '10%', 'rate' => 10, 'mark' => null],
            ['id' => 2, 'sort_id' => 20, 'name' => '8% (軽減)', 'rate' => 8, 'mark' => '(軽)'],
            ['id' => 3, 'sort_id' => 30, 'name' => '8%', 'rate' => 8, 'mark' => null],
            ['id' => 4, 'sort_id' => 40, 'name' => '5%', 'rate' => 5, 'mark' => null],
            ['id' => 5, 'sort_id' => 50, 'name' => '3%', 'rate' => 3, 'mark' => null],
            ['id' => 6, 'sort_id' => 60, 'name' => '非課税', 'rate' => 0, 'mark' => '(非)'],
            ['id' => 7, 'sort_id' => 70, 'name' => '不課税', 'rate' => 0, 'mark' => '(不)']
        ]);
    }
}
