<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_AdminSewa extends Model
{
    use HasFactory;
    protected $table = 'admin_sewas';
    protected $primaryKey = 'id_admin_sewa';
    protected $guarded = ['id_admin_sewa'];

    public function user()
    {
        return $this->belongsTo(M_User::class, 'id_user');
    }

    public function pemilikRental()
    {
        return $this->belongsTo(M_PemilikRental::class, 'id_pemilik_rental');
    }
}
