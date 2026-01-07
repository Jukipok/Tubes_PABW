<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Pemesanan extends Model
{
    use HasFactory;
    protected $table = 'pemesanans';
    protected $primaryKey = 'id_pemesanan';
    protected $guarded = ['id_pemesanan'];

    // Relationships
    public function pelanggan()
    {
        return $this->belongsTo(M_Pelanggan::class, 'id_pelanggan');
    }

    public function kendaraan()
    {
        return $this->belongsTo(M_KendaraanListrik::class, 'id_kendaraan');
    }

    public function pembayaran()
    {
        return $this->hasOne(M_Pembayaran::class, 'id_pemesanan');
    }
    public function denda()
    {
        return $this->hasOne(M_Denda::class, 'id_pemesanan');
    }

    public function xenditPayment()
    {
        return $this->hasOne(M_XenditPayment::class, 'id_pemesanan');
    }
}
