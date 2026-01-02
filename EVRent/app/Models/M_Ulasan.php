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

    public function pemesanan()
    {
        return $this->belongsTo(M_Pemesanan::class, 'id_pemesanan');
    }
}
