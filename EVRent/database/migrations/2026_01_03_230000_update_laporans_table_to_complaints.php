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
        Schema::table('laporans', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['periode', 'rekap_penyewaan', 'rekap_pendapatan', 'rekap_rating']);
            
            // Add new columns
            $table->foreignId('id_pemesanan')->after('id_laporan')->constrained('pemesanans', 'id_pemesanan')->onDelete('cascade');
            $table->enum('jenis_laporan', ['kerusakan_awal', 'kerusakan_akhir', 'lainnya'])->after('id_pemesanan');
            $table->text('deskripsi_masalah')->after('jenis_laporan');
            $table->string('foto_bukti')->nullable()->after('deskripsi_masalah');
            $table->string('status_laporan')->default('pending')->after('foto_bukti'); // pending, diproses, selesai
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->string('periode')->nullable();
            $table->double('rekap_penyewaan')->nullable();
            $table->double('rekap_pendapatan')->nullable();
            $table->integer('rekap_rating')->nullable();

            $table->dropForeign(['id_pemesanan']);
            $table->dropColumn(['id_pemesanan', 'jenis_laporan', 'deskripsi_masalah', 'foto_bukti', 'status_laporan']);
        });
    }
};
