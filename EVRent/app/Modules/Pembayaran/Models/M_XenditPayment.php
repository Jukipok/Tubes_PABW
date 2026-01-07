<?php

namespace App\Modules\Pembayaran\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Transaksi\Models\M_Pemesanan;

class M_XenditPayment extends Model
{
    use HasFactory;

    protected $table = 'xendit_payments';

    protected $fillable = [
        'external_id',
        'id_pemesanan',
        'payment_id',
        'status',
        'amount',
        'currency',
        'payment_method',
        'payment_channel',
        'paid_at',
        'raw_response',
    ];

    /**
     * Get the pemesanan that owns the payment.
     */
    public function pemesanan()
    {
        return $this->belongsTo(M_Pemesanan::class, 'id_pemesanan');
    }
}
