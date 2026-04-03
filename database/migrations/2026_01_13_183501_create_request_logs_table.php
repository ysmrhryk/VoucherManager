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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->constrained('users')->nullable(); // 誰が
            $table->string('method'); // Http Request Method
            $table->string('path'); // アクセス先のパス
            $table->json('payload'); // リクエストの内容
            $table->string('ip_address')->nullable(); // アクセス元
            $table->string('user_agent')->nullable(); // ユーザーエージェント
            $table->string('status_code')->nullable(); // ステータスコード
            $table->string('processing_time')->nullable(); // 処理時間
            $table->string('referer')->nullable(); // リファラ
            $table->timestamps(); // タイムスタンプ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
