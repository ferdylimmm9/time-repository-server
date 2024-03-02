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
        Schema::create('jurusan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fakultas_id');
            $table->string('nama', 50);
            $table->string('kode', 20);
            $table->timestampTz('waktu_dibuat', 6);
            $table->timestampTz('waktu_diubah', 6);

            $table->foreign('fakultas_id')->references('id')->on('fakultas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};
