<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            ['id' => 1, 'sort_id' => 1, 'value' => '振込'],
            ['id' => 2, 'sort_id' => 2, 'value' => '現金'],
            ['id' => 3, 'sort_id' => 3, 'value' => '小切手'],
            ['id' => 4, 'sort_id' => 4, 'value' => '振込手数料'],
            ['id' => 5, 'sort_id' => 5, 'value' => '相殺'],
            ['id' => 6, 'sort_id' => 6, 'value' => '値引'],
        ]);
    }
}
