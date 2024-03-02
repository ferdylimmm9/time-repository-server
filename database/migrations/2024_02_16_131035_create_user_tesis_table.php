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
        Schema::create('user_tesis', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('tesis_id');
            $table->integer('urutan');

            $table->foreign('user_id')->references('id')->on('user');
            $table->foreign('tesis_id')->references('id')->on('tesis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tesis');
    }
};
