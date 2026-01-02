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
        Schema::table('pemilik_rentals', function (Blueprint $table) {
            $table->string('nama_rental')->nullable()->after('id_user');
            $table->double('latitude')->nullable()->after('lokasi_rental');
            $table->double('longitude')->nullable()->after('latitude');
        });

        Schema::table('kendaraan_listriks', function (Blueprint $table) {
            $table->foreignId('id_pemilik_rental')->nullable()->after('id_kendaraan')->constrained('pemilik_rentals', 'id_pemilik_rental')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendaraan_listriks', function (Blueprint $table) {
            $table->dropForeign(['id_pemilik_rental']);
            $table->dropColumn('id_pemilik_rental');
        });

        Schema::table('pemilik_rentals', function (Blueprint $table) {
            $table->dropColumn(['nama_rental', 'latitude', 'longitude']);
        });
    }
};
