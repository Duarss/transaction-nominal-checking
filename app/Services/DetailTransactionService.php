<?php

namespace App\Services;

class DetailTransactionService
{
    public function fetch(array $request) : object
    {
        return (object) [
            'detail_transaction' => [
                'item_index' => $request['item_index'] ?? null,
                'payment_type' => $request['payment_type'] ?? null,
                'amount' => $request['amount'] ?? null,
                'bank' => $request['bank'] ?? null,
                'bank_doc' => $request['bank_doc'] ?? null,
                'bank_due' => $request['bank_due'] ?? null,
                'location' => $request['location'] ?? null,
            ],
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
}

?>