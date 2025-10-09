<?php

namespace App\Jobs;

use App\Models\DetailTransaction;
use App\Models\MainPayment;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchNewTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 1. Get doc_ids that already exist in transactions
        $existingDocIds = Transaction::pluck('doc_id')->toArray();

        // 2. Fetch only new main payments
        $newMainPayments = MainPayment::whereNotIn('doc_id', $existingDocIds)
            ->with('mainPaymentDetails')
            ->get();

        Log::info('Fetched new transactions: ' . $newMainPayments->count());

        foreach ($newMainPayments as $mainPayment) {
             // Find existing transaction by doc_id
            $transaction = Transaction::where('doc_id', $mainPayment->doc_id)->first();

            if ($transaction) {
                // Only update if not approved
                if ($transaction->is_approved == 0) {
                    $transaction->update([
                        'date'          => $mainPayment->tanggal,
                        'doc_call_id'   => $mainPayment->doc_call_id,
                        'branch_code'   => $mainPayment->branch_id,
                        'sales_code'    => $mainPayment->sales_id,
                        'customer_code' => $mainPayment->customer_id,
                        'total'         => $mainPayment->total,
                        'paid_amount'   => null,
                        'created_on'    => $mainPayment->created_on,
                        'last_updated'  => $mainPayment->last_updated,
                        'created_by'    => $mainPayment->created_by,
                        'updated_by'    => $mainPayment->updated_by,
                    ]);
                }
            } else {
                // Create new transaction if not exists
                $transaction = Transaction::create([
                    'doc_id'        => $mainPayment->doc_id,
                    'date'          => $mainPayment->tanggal,
                    'doc_call_id'   => $mainPayment->doc_call_id,
                    'branch_code'   => $mainPayment->branch_id,
                    'sales_code'    => $mainPayment->sales_id,
                    'customer_code' => $mainPayment->customer_id,
                    'total'         => $mainPayment->total,
                    'paid_amount'   => null,
                    'created_on'    => $mainPayment->created_on,
                    'last_updated'  => $mainPayment->last_updated,
                    'created_by'    => $mainPayment->created_by,
                    'updated_by'    => $mainPayment->updated_by,
                ]);
            }

            // Insert all detail transactions for this doc_id (optional: upsert if needed)
            $details = $mainPayment->mainPaymentDetails;
            $sum = 0;
            foreach ($details as $detail) {
                DetailTransaction::updateOrCreate(
                    [
                        'doc_id'     => $detail->doc_id,
                        'item_index' => $detail->item_index,
                    ],
                    [
                        'payment_type' => $detail->payment_type,
                        'amount'       => $detail->amount,
                        'bank'         => $detail->bank,
                        'bank_doc'     => $detail->bank_doc,
                        'bank_due'     => $detail->bank_due,
                        'location'     => $detail->location,
                    ]
                );
                $sum += $detail->amount;
            }

            // Update paid_amount for this transaction
            $transaction->paid_amount = $sum;
            $transaction->save();
        }
    }
}
