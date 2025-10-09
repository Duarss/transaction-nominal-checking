<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 20;
        $branches = Branch::with('stores')->get();

        // Generate a pool of random sales codes
        $salesCodes = [];
        foreach (range(1, 10) as $n) {
            $salesCodes[] = 'SALES' . now()->format('ymd') . strtoupper(substr(uniqid(), -3)) . $n;
        }

        foreach (range(1, $count) as $i) {
            $now = now();
            // Pick a random branch
            $branch = $branches->random();
            $branchCode = $branch->code;

            // Pick a random store from this branch
            $store = $branch->stores->random();
            $storeCode = $store->code;

            // Pick a random sales code from the pool
            $salesCode = $salesCodes[array_rand($salesCodes)];

            Transaction::create([
                'doc_id' => 'TRX' . $now->format('ymdHis') . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date' => now()->subDays(rand(0, 30)),
                'doc_call_id' => 'CALL' . $now->format('His') . rand(100, 999),
                'branch_code' => $branchCode,
                'sales_code' => $salesCode,
                'customer_code' => $storeCode,
                'total' => rand(10000, 1000000),
                'paid_amount' => null,
                'created_on' => now(),
                'last_updated' => now(),
                'created_by' => $salesCode, // Use sales_code
                'updated_by' => $salesCode, // Use sales_code
            ]);
            usleep(100000); // sleep 0.1s to ensure uniqueness if needed
        }
    }
}
