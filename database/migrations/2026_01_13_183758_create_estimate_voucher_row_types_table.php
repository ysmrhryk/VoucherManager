<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estimate_voucher_row_types', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('sort_id'); // 表示順を調整する用
            $table->string('value'); // 売上、返品、値引、メモ、など。
        });

        Artisan::call('db:seed', ['--class' => 'EstimateVoucherRowTypesSeeder', '--force' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_voucher_row_types');
    }
};
