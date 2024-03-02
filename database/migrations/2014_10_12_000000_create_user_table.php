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
        Schema::create('user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jurusan_id');
            $table->string('nomor_identitas', 20)->unique();
            $table->string('nama_depan', 50);
            $table->string('nama_tengah', 50)->nullable();
            $table->string('nama_belakang', 50)->nullable();
            $table->string('tipe_user', 20);
            $table->string('password');
            $table->timestampTz('waktu_dibuat', 6);
            $table->timestampTz('waktu_diubah', 6);

            $table->foreign('jurusan_id')->references('id')->on('jurusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
