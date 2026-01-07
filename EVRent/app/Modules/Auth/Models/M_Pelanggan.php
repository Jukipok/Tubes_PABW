<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggans';
    protected $primaryKey = 'id_pelanggan';
    protected $guarded = ['id_pelanggan'];

    public function user()
    {
        return $this->belongsTo(M_User::class, 'id_user');
    }

    public function pemesanan() // UML says 1..*
    {
        return $this->hasMany(M_Pemesanan::class, 'id_pelanggan');
    }

    public function ulasans()
    {
        return $this->hasMany(M_Ulasan::class, 'id_pelanggan');
    }
}
