<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Laporan extends Model
{
    use HasFactory;
    protected $table = 'laporans';
    protected $primaryKey = 'id_laporan';
    protected $guarded = ['id_laporan'];

    public function pemesanan()
    {
        return $this->belongsTo(M_Pemesanan::class, 'id_pemesanan');
    }
}
