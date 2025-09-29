<?php

namespace Database\Seeders;

use App\Models\DetailTransaction;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetailTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentTypes = ['tunai', 'transfer', 'potongan CN', 'cek/giro/sup'];
        $transactions = Transaction::all();

        foreach ($transactions as $trx) {
            $total = $trx->total;
            $detailCount = rand(1, 5); // 1 or more detail transactions per transaction
            $remaining = $total;
            $amounts = [];

            // Generate random amounts for each detail except the last
            for ($j = 1; $j < $detailCount; $j++) {
                $max = $remaining - ($detailCount - $j); // ensure at least 1 for each remaining
                $amount = rand(1, max(1, $max));
                $amounts[] = $amount;
                $remaining -= $amount;
            }
            // Last detail gets the remaining amount to ensure sum matches total
            $amounts[] = $remaining;

            for ($j = 1; $j <= $detailCount; $j++) {
                DetailTransaction::create([
                    'doc_id' => $trx->doc_id,
                    'item_index' => $j,
                    'payment_type' => $paymentTypes[array_rand($paymentTypes)],
                    'amount' => $amounts[$j-1],
                    'bank' => fake()->company(),
                    'bank_doc' => 'BD' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'bank_due' => now()->addDays(rand(1, 30)),
                    'location' => fake()->address(),
                ]);
            }

            $sum = DetailTransaction::where('doc_id', $trx->doc_id)->sum('amount');
            $trx->paid_amount = $sum;
            $trx->save();
        }
    }
}
