<?php

namespace App\Services;

class ActionLogService
{
    public function fetch(array $request) : object
    {
        return (object) [
            'action_log' => [
                // 'transaction_code' => $request['transaction_code'],
                'status' => $request['status'] ?? null,
                // 'done_by' => $request['done_by'] ?? null,
            ],
            'transaction' => [
                'doc_id' => $request['doc_id'],
                'sales_code' => $request['sales_code'] ?? null,
                'customer_code' => $request['customer_code'] ?? null,
                'total' => $request['total'] ?? null,
                'created_on' => $request['created_on'] ?? null,
                'last_updated' => $request['last_updated'] ?? null,
                // 'created_by' => $request['created_by'] ?? null,
                // 'updated_by' => $request['updated_by'] ?? null,
            ],
            'user' => [
                'username' => $request['username'] ?? null,
                'name' => $request['name'] ?? null,
                'email' => $request['email'] ?? null,
                'role' => $request['role'] ?? null,
            ],
        ];
    }
}

?>