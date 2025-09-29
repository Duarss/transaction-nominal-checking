<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\MainPayment;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class MainPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 100;
        $branches = Branch::with('stores')->get();
        
        // Generate a pool of random sales codes
        $salesCodes = [];
        foreach (range(1, 10) as $n) {
            $salesCodes[] = 'SALES' . now()->format('ymd') . strtoupper(substr(uniqid(), -3)) . $n;
        }

        // Get existing transactions to copy all columns for overlap
        $existingTransactions = Transaction::all();
        $existingCount = $existingTransactions->count();

        foreach (range(1, $count) as $i) {
            $now = now();

            if ($i <= $existingCount) {
                // Overlap doc_id, but change some columns
                $trx = $existingTransactions[$i - 1];
                MainPayment::create([
                    'doc_id'        => $trx->doc_id,
                    'tanggal'       => $trx->date,
                    'doc_call_id'   => $trx->doc_call_id,
                    'branch_id'     => $trx->branch_code,
                    // CHANGE sales_id and total for testing
                    'sales_id'      => $salesCodes[array_rand($salesCodes)],
                    'customer_id'   => $trx->customer_code,
                    'total'         => $trx->total + rand(1000, 9999), // change total
                    'created_on'    => $trx->created_on,
                    'last_updated'  => now(), // change last_updated
                    'created_by'    => $trx->created_by,
                    'updated_by'    => $trx->updated_by,
                ]);
            } else {
                // Generate new unique data
                $branch = $branches->random();
                $branchCode = $branch->code;
                $store = $branch->stores->random();
                $storeCode = $store->code;
                $salesCode = $salesCodes[array_rand($salesCodes)];

                MainPayment::create([
                    'doc_id' => 'TRX' . $now->format('ymdHis') . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'tanggal' => now()->subDays(rand(0, 30)),
                    'doc_call_id' => 'CALL' . $now->format('His') . rand(100, 999),
                    'branch_id' => $branchCode,
                    'sales_id' => $salesCode,
                    'customer_id' => $storeCode,
                    'total' => rand(10000, 1000000),
                    'created_on' => now(),
                    'last_updated' => now(),
                    'created_by' => $salesCode,
                    'updated_by' => $salesCode,
                ]);
                usleep(100000);
            }
        }
    }
}
