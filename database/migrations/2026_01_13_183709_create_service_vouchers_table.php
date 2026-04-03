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
        Schema::create('service_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date'); // 伝票日付
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete(); // 請求書番号
            $table->text('customer_note')->nullable(); // 顧客向け備考欄(請求書等に表示)
            $table->text('internal_note')->nullable(); // 社内向け備考欄

            $table->foreignId('user_id')->constrained(); // 伝票担当者 作者はログに残るので不要
            $table->string('user_name'); // users.name スナップショット 伝票に表示されるのはこれくらいなので

            $table->foreignId('client_id')->constrained(); // 請求先顧客
            $table->string('client_name'); // clients.name スナップショット
            $table->string('client_postal')->nullable(); // clients.postal スナップショット 未記入OK
            $table->string('client_address')->nullable(); // clients.address スナップショット 未記入OK
            $table->string('client_tel')->nullable(); // clients.tel スナップショット 未記入OK
            $table->string('client_fax')->nullable(); // clients.fax スナップショット 未記入OK

            $table->string('extension_client_name')->nullable(); // 表示名を追加できるようにする
            $table->foreignId('name_suffix_id')->nullable()->constrained(); // 選択式になるので

            $table->string('my_name')->nullable(); // settings.name スナップショット
            $table->string('my_postal')->nullable(); // settings.postal スナップショット
            $table->string('my_address')->nullable(); // settings.address スナップショット
            $table->string('my_tel')->nullable(); // settings.tel スナップショット
            $table->string('my_fax')->nullable(); // settings.fax スナップショット

            $table->integer('total_net_amount')->default(0); // 税抜き合計
            
            $table->timestamps();

            $table->index(['client_id', 'date']);
            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_vouchers');
    }
};
