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
        // 1. Pelanggan
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id('id_pelanggan');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Pemilik Rental
        Schema::create('pemilik_rentals', function (Blueprint $table) {
            $table->id('id_pemilik_rental');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('lokasi_rental');
            $table->timestamps();
        });

        // 3. Admin Sewa
        Schema::create('admin_sewas', function (Blueprint $table) {
            $table->id('id_admin_sewa');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_pemilik_rental')->constrained('pemilik_rentals', 'id_pemilik_rental')->onDelete('cascade');
            $table->string('lokasi_kantor');
            $table->timestamps();
        });

        // 4. Kendaraan Listrik
        Schema::create('kendaraan_listriks', function (Blueprint $table) {
            $table->id('id_kendaraan');
            $table->string('merk_kendaraan');
            $table->string('tipe_kendaraan');
            $table->string('plat_nomor')->unique();
            $table->integer('harga_perjam');
            $table->enum('status_ketersediaan', ['tersedia', 'disewa', 'perbaikan'])->default('tersedia');
            $table->text('gambar_kendaraan')->nullable(); // Additional for UI
            $table->timestamps();
        });

        // 5. Pemesanan
        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id('id_pemesanan');
            $table->foreignId('id_pelanggan')->constrained('pelanggans', 'id_pelanggan')->onDelete('cascade');
            $table->foreignId('id_kendaraan')->constrained('kendaraan_listriks', 'id_kendaraan')->onDelete('cascade');
            $table->date('tanggal_sewa');
            $table->date('tanggal_kembali');
            $table->integer('durasi_sewa')->comment('dalam jam or hari'); 
            $table->double('total_biaya');
            $table->enum('status_sewa', ['menunggu_pembayaran', 'dibayar', 'berlangsung', 'selesai', 'batal'])->default('menunggu_pembayaran');
            $table->timestamps();
        });

        // 6. Pembayaran
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_pemesanan')->constrained('pemesanans', 'id_pemesanan')->onDelete('cascade');
            $table->string('metode_pembayaran');
            $table->integer('jumlah_bayar');
            $table->dateTime('tanggal_bayar');
            $table->string('bukti_transfer')->nullable();
            $table->string('status_bayar')->default('pending');
            $table->timestamps();
        });

        // 7. Denda
        Schema::create('dendas', function (Blueprint $table) {
            $table->id('id_denda');
            $table->foreignId('id_pemesanan')->constrained('pemesanans', 'id_pemesanan')->onDelete('cascade');
            $table->string('jenis_denda');
            $table->double('total_denda');
            $table->string('status_denda');
            $table->timestamps();
        });

        // 8. Ulasan
        Schema::create('ulasans', function (Blueprint $table) {
            $table->id('id_ulasan');
            $table->foreignId('id_pemesanan')->constrained('pemesanans', 'id_pemesanan')->onDelete('cascade'); // Link to order mostly
            $table->integer('rating');
            $table->text('komentar');
            $table->timestamps();
        });

        // 9. Laporan (Summary table or just view? Using table as per M_Laporan might imply storage)
        Schema::create('laporans', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->string('periode');
            $table->double('rekap_penyewaan');
            $table->double('rekap_pendapatan');
            $table->integer('rekap_rating');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
        Schema::dropIfExists('ulasans');
        Schema::dropIfExists('dendas');
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('pemesanans');
        Schema::dropIfExists('kendaraan_listriks');
        Schema::dropIfExists('admin_sewas');
        Schema::dropIfExists('pemilik_rentals');
        Schema::dropIfExists('pelanggans');
    }
};
