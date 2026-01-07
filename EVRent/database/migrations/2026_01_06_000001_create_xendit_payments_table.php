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
        Schema::create('xendit_payments', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index(); // Matches the invoice external_id
            $table->foreignId('id_pemesanan')->constrained('pemesanans', 'id_pemesanan')->onDelete('cascade');
            $table->string('payment_id')->nullable(); // Xendit Payment ID / Invoice ID
            $table->string('status'); // PENDING, PAID, EXPIRED, FAILED
            $table->double('amount');
            $table->string('currency')->default('IDR');
            $table->string('payment_method')->nullable(); // e.g., BANK_TRANSFER, EWALLET
            $table->string('payment_channel')->nullable(); // e.g., BCA, OVO
            $table->timestamp('paid_at')->nullable();
            $table->text('raw_response')->nullable(); // Store the full JSON callback for debugging
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xendit_payments');
    }
};
