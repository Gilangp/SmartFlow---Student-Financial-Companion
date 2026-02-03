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
        /**
         * ================================
         * 1. USER CONTOH (TESTING)
         * ================================
         */
        $user = User::create([
            'name' => 'Mahasiswa Teladan',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'salary_date' => 30,
        ]);

        /**
         * ================================
         * 2. KATEGORI DEFAULT (SYSTEM)
         * user_id = null
         * ================================
         */
        $categories = [
            // NEED
            ['name' => 'Makan & Minum', 'type' => 'need'],
            ['name' => 'Transportasi', 'type' => 'need'],
            ['name' => 'Kos & Listrik', 'type' => 'need'],
            ['name' => 'Kuota Internet', 'type' => 'need'],
            ['name' => 'Perlengkapan Kuliah', 'type' => 'need'],

            // WANT
            ['name' => 'Hiburan / Nongkrong', 'type' => 'want'],
            ['name' => 'Belanja / Outfit', 'type' => 'want'],
            ['name' => 'Langganan Digital', 'type' => 'want'],
            ['name' => 'Skincare / Perawatan', 'type' => 'want'],
            ['name' => 'Hobi & Game', 'type' => 'want'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'user_id' => null,
                'name'    => $category['name'],
                'type'    => $category['type'],
            ]);
        }

        /**
         * ================================
         * 3. POCKET DEFAULT USER
         * ================================
         */

        // A. MAIN
        Pocket::create([
            'user_id' => $user->id,
            'name'    => 'Dompet Harian',
            'type'    => 'main',
            'balance' => 1_500_000,
        ]);

        // B. EMERGENCY
        Pocket::create([
            'user_id'       => $user->id,
            'name'          => 'Dana Darurat',
            'type'          => 'emergency',
            'balance'       => 500_000,
            'target_amount' => 2_000_000,
            'is_locked'     => true,
        ]);

        // C. SAVINGS
        Pocket::create([
            'user_id'   => $user->id,
            'name'      => 'Tabungan Emas',
            'type'      => 'savings',
            'balance'   => 1_000_000,
            'is_locked' => true,
        ]);

        // D. WISHLIST (boleh lebih dari satu nantinya)
        Pocket::create([
            'user_id'       => $user->id,
            'name'          => 'Beli Sepatu Baru',
            'type'          => 'wishlist',
            'balance'       => 300_000,
            'target_amount' => 1_200_000,
            'is_completed'  => false,
        ]);
    }
}
