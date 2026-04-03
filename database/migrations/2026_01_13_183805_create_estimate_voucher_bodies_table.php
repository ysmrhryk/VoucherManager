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
        Schema::create('estimate_voucher_bodies', function (Blueprint $table) {
            $table->id();
            
            $table->foreignUuId('estimate_voucher_id')->constrained()->cascadeOnDelete(); // ヘッダのID
            $table->foreignId('estimate_voucher_row_type_id')->constrained(); // 行区分のID
            $table->integer('line_number'); // 行番号
            
            $table->integer('quantity')->nullable(); // 数量
            $table->integer('unit_price')->nullable(); // 単価
            $table->string('content')->nullable(); // 品名とか、サービス名称とか

            $table->foreignId('tax_rate_id')->nullable()->constrained(); // 税率区分
            $table->string('tax_rate_name')->nullable(); // tax_rates.nameのスナップショット
            $table->integer('tax_rate_rate')->nullable(); // tax_rates.rateのスナップショット
            $table->string('tax_rate_mark')->nullable(); // tax_rates.markのスナップショット

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_voucher_bodies');
    }
};
