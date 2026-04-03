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
        Schema::create('name_suffixes', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('sort_id')->nullable();
            $table->string('value');
        });

        Artisan::call('db:seed', ['--class' => 'NameSuffixesSeeder', '--force' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('name_suffixes');
    }
};
