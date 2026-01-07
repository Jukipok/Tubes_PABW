<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_PemilikRental extends Model
{
    use HasFactory;
    protected $table = 'pemilik_rentals';
    protected $primaryKey = 'id_pemilik_rental';
    protected $guarded = ['id_pemilik_rental'];

    public function user()
    {
        return $this->belongsTo(M_User::class, 'id_user');
    }

    public function ulasans()
    {
        return $this->hasMany(M_Ulasan::class, 'id_pemilik_rental');
    }
}
