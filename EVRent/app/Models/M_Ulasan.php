<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Ulasan extends Model
{
    use HasFactory;
    protected $table = 'ulasans';
    protected $primaryKey = 'id_ulasan';
    protected $guarded = ['id_ulasan'];

    public function pemilikRental()
    {
        return $this->belongsTo(M_PemilikRental::class, 'id_pemilik_rental');
    }

    public function pelanggan()
    {
        return $this->belongsTo(M_Pelanggan::class, 'id_pelanggan');
    }
}
