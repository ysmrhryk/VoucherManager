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
        Schema::create('refund_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date'); // 発行日
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete(); // 請求書番号
            $table->text('internal_note')->nullable(); // 社内向け備考欄

            $table->foreignId('client_id')->constrained(); // 請求先顧客
            $table->string('client_name'); // clients.name スナップショット
            $table->string('client_postal')->nullable(); // clients.postal スナップショット 未記入OK
            $table->string('client_address')->nullable(); // clients.address スナップショット 未記入OK
            $table->string('client_tel')->nullable(); // clients.tel スナップショット 未記入OK
            $table->string('client_fax')->nullable(); // clients.fax スナップショット 未記入OK

            $table->string('my_name')->nullable(); // settings.name スナップショット
            $table->string('my_postal')->nullable(); // settings.postal スナップショット
            $table->string('my_address')->nullable(); // settings.address スナップショット
            $table->string('my_tel')->nullable(); // settings.tel スナップショット
            $table->string('my_fax')->nullable(); // settings.fax スナップショット

            $table->integer('total_amount')->default(0); // 税抜き

            $table->timestamps();

            $table->index('invoice_id');
            $table->index(['client_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_vouchers');
    }
};
