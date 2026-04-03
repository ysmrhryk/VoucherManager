<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NameSuffixesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('name_suffixes')->insert([
            ['id' => 1, 'sort_id' => 10, 'value' => '御中'],
            ['id' => 2, 'sort_id' => 20, 'value' => '様']
        ]);
    }
}
