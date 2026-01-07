<?php

namespace App\Modules\Pembayaran\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayarans';
    protected $primaryKey = 'id_pembayaran';
    protected $guarded = ['id_pembayaran'];

    public function pemesanan()
    {
        return $this->belongsTo(\App\Modules\Transaksi\Models\M_Pemesanan::class, 'id_pemesanan');
    }
}
