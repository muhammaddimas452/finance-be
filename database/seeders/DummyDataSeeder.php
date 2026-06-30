<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT DATA USER (ID 1 & 2)
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Dimas Wijaya',
                'email' => 'dimas@mooney.com',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Budi Santoso',
                'email' => 'budi@mooney.com',
                'password' => Hash::make('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // 2. BUAT DATA WALLETS (Untuk User 1 dan User 2)
        DB::table('wallets')->insert([
            // Wallets untuk User 1
            [
                'id' => 1,
                'user_id' => 1,
                'name' => 'Rekening Utama',
                'balance' => 5000000.00,
                'icon' => 'CreditCard',
                'is_primary' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'name' => 'Dompet Digital (Dana)',
                'balance' => 350000.00,
                'icon' => 'Smartphone',
                'is_primary' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Wallets untuk User 2
            [
                'id' => 3,
                'user_id' => 2,
                'name' => 'Cash',
                'balance' => 1000000.00,
                'icon' => 'Wallet',
                'is_primary' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // 3. BUAT DATA CATEGORIES (Income & Expense)
        DB::table('categories')->insert([
            // Kategori untuk User 1
            ['id' => 1, 'user_id' => 1, 'name' => 'Gaji Bulanan', 'type' => 'income', 'icon' => 'Briefcase', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'user_id' => 1, 'name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'Utensils', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'user_id' => 1, 'name' => 'Transportasi', 'type' => 'expense', 'icon' => 'Car', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Kategori untuk User 2
            ['id' => 4, 'user_id' => 2, 'name' => 'Freelance', 'type' => 'income', 'icon' => 'Laptop', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'user_id' => 2, 'name' => 'Kopi & Nongkrong', 'type' => 'expense', 'icon' => 'Coffee', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 4. BUAT DATA TRANSACTIONS
        DB::table('transactions')->insert([
            // Transaksi User 1 (Dimas)
            [
                'user_id' => 1,
                'wallet_id' => 1, // Rekening Utama
                'category_id' => 1, // Gaji Bulanan
                'title' => 'Gaji PT Kayaba',
                'amount' => 5500000.00,
                'type' => 'income',
                'date' => Carbon::now()->format('Y-m-d'),
                'notes' => 'Gaji pokok bulan ini',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'wallet_id' => 1, // Rekening Utama
                'category_id' => 2, // Makanan
                'title' => 'Makan Marugame Udon',
                'amount' => 65000.00,
                'type' => 'expense',
                'date' => Carbon::now()->format('Y-m-d'),
                'notes' => 'Makan malam bareng tim',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 1,
                'wallet_id' => 2, // Dana
                'category_id' => 3, // Transportasi
                'title' => 'Isi Bensin Shell',
                'amount' => 50000.00,
                'type' => 'expense',
                'date' => Carbon::now()->subDay()->format('Y-m-d'), // Kemarin
                'notes' => 'Bensin motor full tank',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Transaksi User 2 (Budi)
            [
                'user_id' => 2,
                'wallet_id' => 3, // Cash
                'category_id' => 4, // Freelance
                'title' => 'Project Landing Page',
                'amount' => 1200000.00,
                'type' => 'income',
                'date' => Carbon::now()->format('Y-m-d'),
                'notes' => 'DP Project Web',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 2,
                'wallet_id' => 3, // Cash
                'category_id' => 5, // Kopi
                'title' => 'Beli Tomoro Coffee',
                'amount' => 25000.00,
                'type' => 'expense',
                'date' => Carbon::now()->format('Y-m-d'),
                'notes' => 'Es Kopi Susu Gula Aren',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
