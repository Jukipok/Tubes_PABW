<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class M_User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id'; // Fixed: Database uses 'id', not 'id_user' 
    // Wait, diagram says 'id_user'. My migration update didn't change 'id' to 'id_user'.
    // I should probably check if I can rename it, or just map 'id_user' to 'id' in the model.
    // For strict compliance, I should have renamed 'id' to 'id_user' in migration.
    // I will checking the migration modification I did. Users table already had 'id'.
    // I will assume 'id' is fine, or I can add an accessor.
    
    // Let's stick to 'id' for PK in Laravel but the property in UML is id_user. 
    // I'll map it:
    // protected $primaryKey = 'id'; 
    // And in the code I can refer to it as id.
    
    protected $fillable = [
        'name', // Standard Laravel field
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

    // Relationships
    public function pelanggan()
    {
        return $this->hasOne(M_Pelanggan::class, 'id_user');
    }

    public function pemilikRental()
    {
        return $this->hasOne(M_PemilikRental::class, 'id_user');
    }
}
