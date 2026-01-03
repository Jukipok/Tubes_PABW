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
        Schema::table('ulasans', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['id_pemesanan']);
            $table->dropColumn(['id_pemesanan']);
            
            // Add new columns
            $table->foreignId('id_pemilik_rental')->constrained('pemilik_rentals', 'id_pemilik_rental')->onDelete('cascade');
            $table->foreignId('id_pelanggan')->constrained('pelanggans', 'id_pelanggan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ulasans', function (Blueprint $table) {
            $table->dropForeign(['id_pemilik_rental']);
            $table->dropForeign(['id_pelanggan']);
            $table->dropColumn(['id_pemilik_rental', 'id_pelanggan']);
            
            $table->foreignId('id_pemesanan')->constrained('pemesanans', 'id_pemesanan')->onDelete('cascade');
        });
    }
};
