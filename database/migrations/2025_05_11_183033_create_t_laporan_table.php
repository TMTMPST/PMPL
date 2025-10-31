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
        Schema::create('t_laporan', function (Blueprint $table) {
            $table->id('laporan_id');
            $table->foreignId('user_id')->references('user_id')->on('m_user');
            // `t_fasilitas_ruang`.`fasilitas_ruang_id` is defined as string (primary key)
            // so use a string column here and add an explicit foreign key to match types.
            $table->string('fasilitas_ruang_id');
            $table->foreign('fasilitas_ruang_id')->references('fasilitas_ruang_id')->on('t_fasilitas_ruang');
            $table->foreignId('teknisi_id')->references('user_id')->on('m_user')->nullable();
            $table->text('deskripsi_laporan')->nullable();
            $table->longText('lapor_foto')->nullable();
            $table->datetime('lapor_datetime');
            $table->integer('review_pelapor')->nullable();
            $table->text('review_komentar')->nullable();
            $table->boolean('is_verified');
            $table->boolean('is_done');
            $table->integer('spk_kerusakan')->nullable();
            $table->integer('spk_dampak')->nullable();
            $table->integer('spk_frekuensi')->nullable();
            $table->integer('spk_waktu_perbaikan')->nullable();
            $table->datetime('verifikasi_datetime')->nullable();
            $table->datetime('selesai_datetime')->nullable();
            $table->boolean('is_kerjakan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_laporan');
    }
};
