<?php

namespace App\Modules\Kendaraan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_KendaraanListrik extends Model
{
    use HasFactory;
    protected $table = 'kendaraan_listriks';
    protected $primaryKey = 'id_kendaraan';
    protected $guarded = ['id_kendaraan'];

    protected $casts = [
        'gambar_kendaraan' => 'array', // If multiple images
    ];

    public function pemilik()
    {
        return $this->belongsTo(\App\Modules\Auth\Models\M_PemilikRental::class, 'id_pemilik_rental');
    }
}
