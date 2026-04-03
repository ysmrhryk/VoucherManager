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
        Schema::create('refund_voucher_bodies', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('refund_voucher_id')->constrained()->cascadeOnDelete(); // ヘッダのID
            $table->foreignId('payment_method_id')->constrained(); // 支払方法
            $table->string('payment_method_value');
            $table->integer('line_number'); // 行番号

            $table->integer('amount'); // 単価
            $table->string('content')->nullable(); // 品名とか、サービス名称とか

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_voucher_bodies');
    }
};
