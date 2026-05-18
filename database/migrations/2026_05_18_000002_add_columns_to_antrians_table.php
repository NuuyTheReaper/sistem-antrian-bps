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
        Schema::table('antrians', function (Blueprint $table) {
            // Expand keperluan from enum to string to support new services
            $table->string('keperluan', 100)->change();

            // Visitor additional data
            $table->string('nik', 16)->nullable()->after('nomor_hp');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('nik');
            $table->string('email', 100)->nullable()->after('jenis_kelamin');
            $table->string('pekerjaan', 100)->nullable()->after('email');
            $table->string('pendidikan_terakhir', 50)->nullable()->after('pekerjaan');

            // Staff tracking & notes
            $table->foreignId('petugas_id')->nullable()->constrained('users')->onDelete('set null')->after('waktu_selesai');
            $table->text('catatan_petugas')->nullable()->after('petugas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            $table->dropForeign(['petugas_id']);
            $table->dropColumn([
                'nik',
                'jenis_kelamin',
                'email',
                'pekerjaan',
                'pendidikan_terakhir',
                'petugas_id',
                'catatan_petugas',
            ]);
        });
    }
};
