<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel utama untuk menyimpan data antrian pelayanan publik.
     */
    public function up(): void
    {
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();

            // Nomor antrian harian (reset setiap hari)
            $table->integer('nomor_antrian');

            // Data pengunjung
            $table->string('nama', 100);
            $table->text('alamat');
            $table->enum('keperluan', ['Konsultasi', 'Pengaduan']);
            $table->string('nomor_hp', 20);

            // Status antrian: menunggu, dipanggil, selesai, dilewati
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'dilewati'])
                  ->default('menunggu');

            // Tanggal antrian (untuk filtrasi harian & reset)
            $table->date('tanggal_antrian');

            // Waktu dipanggil & waktu selesai (untuk analisis performa)
            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamp('waktu_selesai')->nullable();

            // Soft deletes untuk fitur reset harian
            $table->softDeletes();

            $table->timestamps();

            // Index untuk query harian yang cepat
            $table->index(['tanggal_antrian', 'status']);
            $table->index(['tanggal_antrian', 'nomor_antrian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
