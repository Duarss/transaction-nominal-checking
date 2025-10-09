<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\DetailTransaction;
use App\Models\Transaction;
use Illuminate\Notifications\Action;

class TransactionService
{
    public function fetch(array $request) : object
    {
        return (object) [
            'transaction' => [
                'doc_id' => $request['doc_id'],
                // 'sales_code' => $request['sales_code'] ?? null,
                // 'customer_code' => $request['customer_code'] ?? null,
                'total' => $request['total'] ?? null,
                'created_on' => $request['created_on'] ?? null,
                'last_updated' => $request['last_updated'] ?? null,
                // 'created_by' => $request['created_by'] ?? null,
                // 'updated_by' => $request['updated_by'] ?? null,
                'is_approved' => $request['is_approved'] ?? false,
            ],
            'user' => [
                'code' => $request['code'] ?? null,
                'username' => $request['username'] ?? null,
                'name' => $request['name'] ?? null,
                'email' => $request['email'] ?? null,
                'role' => $request['role'] ?? null,
            ],
        ];
    }

    public function approve(Transaction $transaction, array $request)
    {
        $data = $this->fetch($request);

        $transaction = Transaction::where('doc_id', $data->transaction['doc_id'])
            ->update([
                'paid_amount' => $request['actual_nominal'],
                'is_approved' => true,
                'last_updated' => now(),
                'updated_by' => auth()->user()->code,
            ]);
        
        // Add log to ActionLog table
        if ($transaction) {
            ActionLog::create([
                'transaction_code' => $data->transaction['doc_id'],
                'nominal_before' => $data->transaction['total'],
                'nominal_after' => $request['actual_nominal'],
                'status' => 'approved',
                'done_by' => auth()->user()->username,
            ]);
        } 
                            
        return $transaction;
    }

    public function recheck(Transaction $transaction, array $request)
    {
        $data = $this->fetch($request);

        $transaction = Transaction::where('doc_id', $data->transaction['doc_id'])
            ->update([
                'is_rechecked' => true,
                'last_updated' => now(),
                'updated_by' => auth()->user()->code,
            ]);
        
        // Add log to ActionLog table
        if ($transaction) {
            ActionLog::create([
                'transaction_code' => $data->transaction['doc_id'],
                'nominal_before' => $data->transaction['total'],
                'nominal_after' => $request['actual_nominal'],
                'status' => 'rechecked',
                'done_by' => auth()->user()->username,
            ]);
        } 
                            
        return $transaction;
    }

    // public function unrecheck(Transaction $transaction, array $request)
    // {
    //     $data = $this->fetch($request);

    //     $transaction = Transaction::where('doc_id', $data->transaction['doc_id'])
    //         ->update([
    //             'is_rechecked' => false,
    //             'last_updated' => now(),
    //             'updated_by' => auth()->user()->code,
    //         ]);
        
    //     // Add log to ActionLog table
    //     if ($transaction) {
    //         ActionLog::create([
    //             'transaction_code' => $data->transaction['doc_id'],
    //             'nominal_before' => $data->transaction['total'],
    //             'nominal_after' => $request['actual_nominal'],
    //             'status' => 'unrechecked',
    //             'done_by' => auth()->user()->username,
    //         ]);
    //     } 
                            
    //     return $transaction;
    // }

    public function update(Transaction $transaction, array $request)
    {
        $data = $this->fetch($request);

        $oldPaidAmount = $transaction->paid_amount;

        $updated = Transaction::where('doc_id', $data->transaction['doc_id'])
            ->update([
                'paid_amount' => $request['actual_nominal'],
                'last_updated' => now(),
                'updated_by' => auth()->user()->code,
            ]);

        if ($updated) {
            $trx = Transaction::where('doc_id', $data->transaction['doc_id'])->first();
            ActionLog::create([
                'transaction_code' => $trx->doc_id,
                'nominal_before' => $oldPaidAmount,
                'nominal_after' => $request['actual_nominal'],
                'status' => 'updated',
                'done_by' => auth()->user()->username,
            ]);
        }

        return $transaction;
    }
}
?>