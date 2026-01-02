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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id');
            $table->string('nama_lengkap')->nullable()->after('password');
            $table->string('no_telepon')->nullable()->after('nama_lengkap');
            $table->text('alamat')->nullable()->after('no_telepon');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'nama_lengkap', 'no_telepon', 'alamat', 'jenis_kelamin']);
        });
    }
};
