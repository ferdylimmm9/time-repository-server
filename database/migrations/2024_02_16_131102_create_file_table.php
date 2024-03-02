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
        Schema::create('file', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tesis_id');
            $table->string('nama');
            $table->string('tipe', 20);
            $table->timestampTz('waktu_dibuat', 6);
            $table->timestampTz('waktu_diubah', 6);

            $table->foreign('tesis_id')->references('id')->on('tesis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file');
    }
};
