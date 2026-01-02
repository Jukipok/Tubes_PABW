<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Denda extends Model
{
    use HasFactory;
    protected $table = 'dendas';
    protected $primaryKey = 'id_denda';
    protected $guarded = ['id_denda'];

    public function pemesanan()
    {
        return $this->belongsTo(M_Pemesanan::class, 'id_pemesanan');
    }
}
