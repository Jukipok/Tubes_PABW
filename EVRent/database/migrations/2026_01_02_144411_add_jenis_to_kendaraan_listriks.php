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
        Schema::table('kendaraan_listriks', function (Blueprint $table) {
            $table->enum('jenis', ['mobil', 'motor', 'sepeda'])->after('merk_kendaraan')->default('mobil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendaraan_listriks', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
