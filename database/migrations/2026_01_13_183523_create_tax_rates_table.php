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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('sort_id')->nullable(); // 表示順を調整する用
            $table->string('name'); // 表示名
            $table->integer('rate'); // n% 10とか入ってて、後で/100とかすればいい
            $table->string('mark')->nullable(); // (非)とか(不)とか(軽)みたいなやつ
        });

        Artisan::call('db:seed', ['--class' => 'TaxRatesSeeder', '--force' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
