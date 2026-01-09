<?php

namespace App\Modules\Auth\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class M_User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
        'nama_lengkap',
        'no_telepon',
        'alamat',
        'jenis_kelamin',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function pelanggan()
    {
        return $this->hasOne(M_Pelanggan::class, 'id_user');
    }

    public function pemilikRental()
    {
        return $this->hasOne(M_PemilikRental::class, 'id_user');
    }


    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }
}
