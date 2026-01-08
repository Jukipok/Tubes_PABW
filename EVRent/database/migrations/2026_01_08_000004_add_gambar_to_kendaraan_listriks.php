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
            $table->string('gambar')->nullable()->after('status_ketersediaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendaraan_listriks', function (Blueprint $table) {
            $table->dropColumn('gambar');
        });
    }
};
