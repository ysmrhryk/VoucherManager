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
        Schema::create('service_voucher_footers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignUuId('service_voucher_id')->constrained()->cascadeOnDelete(); // ヘッダのID

            $table->foreignId('tax_rate_id')->nullable()->constrained(); // 税率区分
            $table->integer('tax_rate_rate')->nullable(); // tax_rates.rateのスナップショット
            $table->string('tax_rate_name')->nullable(); // tax_rates.nameのスナップショット

            $table->integer('total_net_amount')->default(0); // 税抜き合計

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_voucher_footers');
    }
};
