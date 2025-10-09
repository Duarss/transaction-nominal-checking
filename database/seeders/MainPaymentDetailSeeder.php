<?php

namespace Database\Seeders;

use App\Models\DetailTransaction;
use App\Models\MainPayment;
use App\Models\MainPaymentDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MainPaymentDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentTypes = ['tunai', 'transfer', 'potongan CN', 'cek/giro/sup'];
        $mainPayments = MainPayment::all();

        foreach ($mainPayments as $mainPayment) {
            $total = $mainPayment->total;
            $detailCount = rand(1, 3);
            $remaining = $total;
            $amounts = [];

            // Generate random amounts for each detail except the last
            for ($j = 1; $j < $detailCount; $j++) {
                $max = $remaining - ($detailCount - $j); // ensure at least 1 for each remaining
                $amount = rand(1000, max(1000, $max));
                $amounts[] = $amount;
                $remaining -= $amount;
            }
            // Last detail gets the remaining amount to ensure sum matches total
            $amounts[] = $remaining;

            for ($j = 1; $j <= $detailCount; $j++) {
                MainPaymentDetail::create([
                    'doc_id'      => $mainPayment->doc_id,
                    'item_index'  => $j,
                    'payment_type'=> $paymentTypes[array_rand($paymentTypes)],
                    'amount'      => $amounts[$j-1],
                    'bank'        => fake()->company(),
                    'bank_doc'    => 'BD' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'bank_due'    => now()->addDays(rand(1, 30)),
                    'location'    => fake()->address(),
                ]);
            }
        }

        // foreach ($mainPayments as $mainPayment) {
        //     $total = $mainPayment->total;
        //     $detailCount = rand(1, 3);
        //     $remaining = $total;

        //     for ($j = 1; $j <= $detailCount; $j++) {
        //         // For the last detail, assign the remaining amount to ensure the sum matches total
        //         if ($j === $detailCount) {
        //             $amount = $remaining;
        //         } else {
        //             $amount = rand(1000, intval($remaining / ($detailCount - $j + 1)));
        //             $remaining -= $amount;
        //         }
        //         MainPaymentDetail::create([
        //             'doc_id'      => $mainPayment->doc_id,
        //             'item_index'  => $j,
        //             'payment_type'=> $paymentTypes[array_rand($paymentTypes)],
        //             'amount'      => $amount,
        //             'bank'        => fake()->company(),
        //             'bank_doc'    => 'BD' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
        //             'bank_due'    => now()->addDays(rand(1, 30)),
        //             'location'    => fake()->address(),
        //         ]);
        //     }
        // }
    }
}
