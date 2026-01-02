<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin EVRent
        \App\Models\M_User::firstOrCreate(
            ['email' => 'admin@evrent.com'],
            [
                'name' => 'Admin EVRent',
                'username' => 'admin',
                'nama_lengkap' => 'Admin EVRent',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin_evrent',
                'no_telepon' => '081234567890',
                'alamat' => 'Kantor Pusat',
                'jenis_kelamin' => 'L'
            ]
        );

        // 3. Create Rental Owners (Mitra) with Specific Locations around Telkom University
        $rentals = [
            [
                'name' => 'Budi Santoso',
                'username' => 'pemilik1',
                'email' => 'owner1@rental.com',
                'phone' => '08123456701',
                'rental_name' => 'Sukabura Rent Point',
                'location' => 'Sukabura, Dayeuhkolot',
                'lat' => -6.9733, 
                'lng' => 107.6304 
            ],
            [
                'name' => 'Siti Aminah',
                'username' => 'pemilik2',
                'email' => 'owner2@rental.com',
                'phone' => '08123456702',
                'rental_name' => 'PBB Eco Trans',
                'location' => 'Jl. Telekomunikasi, Terusan Buah Batu',
                'lat' => -6.9760, 
                'lng' => 107.6320 
            ],
            [
                'name' => 'Ahmad Hidayat',
                'username' => 'pemilik3',
                'email' => 'owner3@rental.com',
                'phone' => '08123456703',
                'rental_name' => 'Bojongsoang Electric',
                'location' => 'Bojongsoang, Kab. Bandung',
                'lat' => -6.9820, 
                'lng' => 107.6315 
            ]
        ];

        $ownerIds = [];

        foreach ($rentals as $rent) {
            $user = \App\Models\M_User::firstOrCreate(
                ['email' => $rent['email']],
                [
                    'name' => $rent['name'],
                    'username' => $rent['username'],
                    'nama_lengkap' => $rent['name'],
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'pemilik_rental',
                    'no_telepon' => $rent['phone'],
                    'alamat' => $rent['location'],
                    'jenis_kelamin' => 'L'
                ]
            );

            $owner = \App\Models\M_PemilikRental::firstOrCreate(
                ['id_user' => $user->id],
                [
                    'lokasi_rental' => $rent['location'],
                    'nama_rental' => $rent['rental_name'],
                    'latitude' => $rent['lat'],
                    'longitude' => $rent['lng']
                ]
            );
            $ownerIds[] = $owner->id_pemilik_rental;
        }

        // 3. Pelanggan
        $pelanggan = \App\Models\M_User::firstOrCreate(
            ['email' => 'ani@gmail.com'],
            [
                'name' => 'Ani Pelanggan',
                'username' => 'user',
                'nama_lengkap' => 'Ani Pelanggan',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'pelanggan',
                'no_telepon' => '081234567892',
                'alamat' => 'Jl. Sudirman No 1',
                'jenis_kelamin' => 'P'
            ]
        );
        \App\Models\M_Pelanggan::firstOrCreate(
            ['id_user' => $pelanggan->id]
        );
        
        // 4. Dummy Vehicles distributed among owners
        // MOBIL
        $cars = [
            ['Hyundai', 'Ioniq 5', 500000],
            ['Wuling', 'Air EV', 250000],
            ['Tesla', 'Model 3', 750000],
            ['Nissan', 'Leaf', 400000],
            ['Toyota', 'bZ4X', 600000]
        ];

        for ($i = 1; $i <= 10; $i++) {
            $car = $cars[array_rand($cars)];
            $ownerId = $ownerIds[array_rand($ownerIds)];

            \App\Models\M_KendaraanListrik::create([
                'merk_kendaraan' => $car[0],
                'tipe_kendaraan' => $car[1],
                'jenis' => 'mobil',
                'plat_nomor' => 'B ' . rand(1000, 9999) . ' ' . Str::upper(Str::random(2)),
                'harga_perjam' => $car[2],
                'status_ketersediaan' => 'tersedia',
                'id_pemilik_rental' => $ownerId
            ]);
        }

        // MOTOR
        $motors = [
            ['Gesits', 'G1', 50000],
            ['Alva', 'One', 60000],
            ['Volta', '401', 40000],
            ['Viar', 'Q1', 35000],
            ['Honda', 'EM1 e', 55000]
        ];

        for ($i = 1; $i <= 10; $i++) {
            $motor = $motors[array_rand($motors)];
            $ownerId = $ownerIds[array_rand($ownerIds)];

            \App\Models\M_KendaraanListrik::create([
                'merk_kendaraan' => $motor[0],
                'tipe_kendaraan' => $motor[1],
                'jenis' => 'motor',
                'plat_nomor' => 'D ' . rand(1000, 9999) . ' ' . Str::upper(Str::random(2)),
                'harga_perjam' => $motor[2],
                'status_ketersediaan' => 'tersedia',
                'id_pemilik_rental' => $ownerId
            ]);
        }

        // SEPEDA (New)
        $bikes = [
            ['Uwinfly', 'Dragonfly', 15000],
            ['Xiaomi', 'Himo Z20', 20000],
            ['Polygon', 'Sage V3', 25000],
            ['Selis', 'Butterfly', 10000],
            ['United', 'E-Trifold', 30000]
        ];

        for ($i = 1; $i <= 10; $i++) {
            $bike = $bikes[array_rand($bikes)];
            $ownerId = $ownerIds[array_rand($ownerIds)];

            \App\Models\M_KendaraanListrik::create([
                'merk_kendaraan' => $bike[0],
                'tipe_kendaraan' => $bike[1],
                'jenis' => 'sepeda',
                'plat_nomor' => 'S ' . rand(1000, 9999) . ' ' . Str::upper(Str::random(2)),
                'harga_perjam' => $bike[2],
                'status_ketersediaan' => 'tersedia',
                'id_pemilik_rental' => $ownerId
            ]);
        }
    }
}
