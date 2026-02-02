<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pocket;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT USER CONTOH (Biar gampang login saat testing)
        // Login pakai: test@example.com / password
        $user = User::create([
            'name' => 'Mahasiswa Teladan',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'salary_date' => 30, // Contoh: Uang kiriman datang tanggal 30
        ]);

        // 2. BUAT KATEGORI BAWAAN (General)
        // user_id = null artinya ini kategori milik sistem (bisa dipakai semua user)
        $categories = [
            ['name' => 'Makan & Minum', 'type' => 'need'],
            ['name' => 'Transportasi (Bensin/Ojol)', 'type' => 'need'],
            ['name' => 'Kos & Listrik', 'type' => 'need'],
            ['name' => 'Kuota Internet', 'type' => 'need'],
            ['name' => 'Perlengkapan Kuliah', 'type' => 'need'],
            ['name' => 'Hiburan/Nongkrong', 'type' => 'want'],
            ['name' => 'Belanja/Outfit', 'type' => 'want'],
            ['name' => 'Langganan (Netflix/Spotify)', 'type' => 'want'],
            ['name' => 'Skincare/Perawatan', 'type' => 'want'],
            ['name' => 'Hobi & Game', 'type' => 'want'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'user_id' => null, // Milik Sistem
                'name' => $cat['name'],
                'type' => $cat['type']
            ]);
        }

        // 3. BUAT 4 KANTONG (POCKETS) UNTUK USER TADI

        // A. Dompet Utama (Main)
        Pocket::create([
            'user_id' => $user->id,
            'name' => 'Dompet Harian',
            'type' => 'main',
            'balance' => 1500000, // Ceritanya sisa uang 1.5jt
        ]);

        // B. Dana Darurat (Emergency)
        Pocket::create([
            'user_id' => $user->id,
            'name' => 'Dana Darurat',
            'type' => 'emergency',
            'balance' => 500000,
            'target_amount' => 2000000, // Target 2 Juta
            'is_locked' => true,
        ]);

        // C. Tabungan Aset (Savings)
        Pocket::create([
            'user_id' => $user->id,
            'name' => 'Tabungan Emas',
            'type' => 'savings',
            'balance' => 1000000,
            'is_locked' => true,
        ]);

        // D. Wishlist (Goals)
        Pocket::create([
            'user_id' => $user->id,
            'name' => 'Beli Sepatu Baru',
            'type' => 'wishlist',
            'balance' => 300000,
            'target_amount' => 1200000, // Harga sepatu 1.2jt
            'is_completed' => false,
        ]);
    }
}
