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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('name_suffix_id')->constrained(); // 必須項目に含める

            $table->string('postal')->nullable();
            $table->string('address')->nullable();
            $table->string('tel')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('website')->nullable();
            $table->integer('initial_previous_invoice_amount')->default(0);

            $table->foreignId('billing_cycle_type_id')->nullable()->constrained();
            $table->tinyInteger('billing_day')->nullable();

            $table->foreignId('payment_method_id')->nullable()->constrained();
            $table->foreignId('transaction_type_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->comment('担当者');

            $table->boolean('allow_login')->default(false);
            $table->string('password')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('client_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('client_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_password_reset_tokens');
        Schema::dropIfExists('client_sessions');
    }
};
