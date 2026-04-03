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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            $table->string('postal')->nullable()->comment('郵便番号');
            $table->string('address')->nullable()->comment('住所');
            $table->string('tel')->nullable()->comment('電話番号');
            $table->string('fax')->nullable()->comment('fax番号');

            $table->string('bank_name')->nullable()->comment('銀行名');
            $table->string('branch_name')->nullable()->comment('支店名');
            $table->string('account_type')->nullable()->comment('預金種目(普通・当座など)');
            $table->string('account_number')->nullable()->comment('口座番号');
            $table->string('account_holder')->nullable()->comment('口座名義');

            $table->timestamps();
        });

        Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
