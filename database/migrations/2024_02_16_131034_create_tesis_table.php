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
        Schema::create('tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jurusan_id');
            $table->uuid('fakultas_id');
            $table->string('judul');
            $table->string('tipe', 20);
            $table->string('status', 20);
            $table->text('abstrak');
            $table->timestampTz('waktu_dibuat', 6);
            $table->timestampTz('waktu_diubah', 6);
            $table->timestampTz('waktu_disetujui', 6)->nullable();

            $table->foreign('jurusan_id')->references('id')->on('jurusan');
            $table->foreign('fakultas_id')->references('id')->on('fakultas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tesis');
    }
};
