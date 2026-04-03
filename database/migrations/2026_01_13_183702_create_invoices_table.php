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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date'); // 締め日

            $table->foreignId('client_id')->constrained(); // 顧客情報
            $table->string('client_code'); // clients.code スナップショット
            $table->string('client_name'); // clients.name スナップショット
            $table->string('client_postal')->nullable(); // clients.postalのスナップショット
            $table->string('client_address')->nullable(); // clients.addressのスナップショット
            $table->string('client_tel')->nullable();
            $table->string('client_fax')->nullable();

            $table->foreignId('name_suffix_id')->constrained();
            $table->string('name_suffix_value'); // name_suffixes.valueのスナップショット

            $table->integer('previous_invoice_amount')->default(0); // 前回請求額
            $table->integer('total_receipt_amount')->default(0); // 御入金額
            $table->integer('total_refund_amount')->default(0); // 返金額
            $table->integer('carried_forward_amount')->default(0); // 繰越額
            $table->integer('total_net_amount')->default(0); // 今回分
            $table->integer('total_tax_amount')->default(0); // 消費税
            $table->integer('total_gross_amount')->default(0); // 今回合計
            $table->integer('current_invoice_amount')->default(0); // 今回請求額

            $table->string('my_name')->nullable(); // settings.name スナップショット
            $table->string('my_postal')->nullable(); // settings.postal スナップショット
            $table->string('my_address')->nullable(); // settings.address スナップショット
            $table->string('my_tel')->nullable(); // settings.tel スナップショット
            $table->string('my_fax')->nullable(); // settings.fax スナップショット

            $table->timestamps();

            $table->unique(['client_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
