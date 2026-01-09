<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\Transaksi\Models\M_Pemesanan;
use Illuminate\Support\Facades\DB;

try {
    echo "--- BOOKING DATA CHECK ---\n";
    $bookings = M_Pemesanan::with(['pelanggan.user', 'kendaraan'])->get();
    
    if ($bookings->isEmpty()) {
        echo "No bookings found.\n";
    } else {
        foreach ($bookings as $b) {
            echo "Booking ID: {$b->id_pemesanan}\n";
            echo " - Status: {$b->status_pemesanan}\n";
            echo " - Pelanggan ID: " . ($b->id_pelanggan ?? 'NULL') . "\n";
            
            if ($b->pelanggan) {
                echo " - Pelanggan Data Relasi: Found (ID: {$b->pelanggan->id_pelanggan})\n";
                if ($b->pelanggan->user) {
                    echo "   - User: {$b->pelanggan->user->name} (ID: {$b->pelanggan->user->id})\n";
                } else {
                    echo "   - User: [MISSING RELATION]\n";
                }
            } else {
                echo " - Pelanggan Data Relasi: [MISSING/NULL]\n";
            }

            echo " - Kendaraan: " . ($b->kendaraan ? $b->kendaraan->merk_kendaraan : '[MISSING]') . "\n";
            echo "--------------------------------\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
