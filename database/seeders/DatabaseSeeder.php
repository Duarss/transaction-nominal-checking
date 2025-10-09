<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 9 branch_admin users
        foreach (range(1, 9) as $i) {
            User::create([
                'code' => 'BRADM' . now()->format('ymdHis') . strtoupper(Str::random(3)),
                'name' => 'Branch Admin ' . $i,
                'email' => "branchadmin{$i}@example.com",
                'username' => "branchadmin{$i}",
                'password' => Hash::make('admin123'),
                'role' => 'branch_admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
            usleep(100000); // Ensure unique code
        }

        // 9 company_admin users
        foreach (range(1, 9) as $i) {
            User::create([
                'code' => 'COMPADM' . now()->format('ymdHis') . strtoupper(Str::random(3)),
                'name' => 'Company Admin ' . $i,
                'email' => "companyadmin{$i}@example.com",
                'username' => "companyadmin{$i}",
                'password' => Hash::make('company123'),
                'role' => 'company_admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
            usleep(100000); // Ensure unique code
        }

        // Optionally, add a test branch admin
        User::factory()->create([
            'code' => 'BRADM' . now()->format('ymdHis') . strtoupper(Str::random(3)),
            'name' => 'Test Branch Admin',
            'email' => 'test.branch.admin@example.com',
            'username' => 'testbranchadmin',
            'password' => Hash::make('admin123'),
            'role' => 'branch_admin',
        ]);

        // Optionally, add a test company admin
        User::factory()->create([
            'code' => 'COMPADM' . now()->format('ymdHis') . strtoupper(Str::random(3)),
            'name' => 'Test Company Admin',
            'email' => 'test.company.admin@example.com',
            'username' => 'testcompanyadmin',
            'password' => Hash::make('company123'),
            'role' => 'company_admin',
        ]);

        $this->call([
            BranchSeeder::class,
            StoreSeeder::class,
            TransactionSeeder::class, // sourced from MainPayment
            DetailTransactionSeeder::class, // sourced from MainPaymentDetail
            MainPaymentSeeder::class, // source transaction data
            MainPaymentDetailSeeder::class, // source transaction detail data
            ActionLogSeeder::class,
        ]);
    }
}
