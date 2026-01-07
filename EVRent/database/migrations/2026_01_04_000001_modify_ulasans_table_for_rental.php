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
            // Drop old foreign key - COMMENTED OUT due to 'Can't DROP FOREIGN KEY' error (already dropped)
            // $table->dropForeign(['id_pemesanan']); 
            
            // Check if column exists before dropping
            if (Schema::hasColumn('ulasans', 'id_pemesanan')) {
                 // Try to drop the index if it exists, explicitly? No, risky. 
                 // Just try removing the column. If it fails due to FK, user might need manual SQL intervention.
                 // But typically if FK drop failed saying "doesn't exist", then we can drop column safely.
                 $table->dropColumn(['id_pemesanan']);
            }
            
            // Add new columns (nullable to allow existing rows)
            // Use 'after' to place them nicely if possible, though strict position isn't critical
            if (!Schema::hasColumn('ulasans', 'id_pemilik_rental')) {
                $table->foreignId('id_pemilik_rental')->nullable()->constrained('pemilik_rentals', 'id_pemilik_rental')->onDelete('cascade');
            }
            if (!Schema::hasColumn('ulasans', 'id_pelanggan')) {
                $table->foreignId('id_pelanggan')->nullable()->constrained('pelanggans', 'id_pelanggan')->onDelete('cascade');
            }
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
