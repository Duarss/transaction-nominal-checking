<?php

namespace App\Services;

use App\Models\Store;

class StoreService
{
    public function fetch(array $request) : object
    {
        return (object) [
            'store' => [
                'code' => $request['code'],
                'name' => $request['name'] ?? null,
                'address' => $request['address'] ?? null,
                // 'branch_code' => $request['branch_code'] ?? null,
                // 'head_of_store' => $request['head_of_store'] ?? null,
            ],
            'branch' => [
                'code' => $request['code'] ?? null,
                'name' => $request['name'] ?? null,
                'address' => $request['address'] ?? null,
            ],
            'user' => [
                'username' => $request['username'] ?? null,
                'name' => $request['name'] ?? null,
                'email' => $request['email'] ?? null,
                'role' => $request['role'] ?? null,
            ],
        ];
    }

    public function update(Store $store, array $request)
    {
        $data = $this->fetch($request);

        return $store->update([
            'name' => $data->store['name'],
            'address' => $data->store['address'],
            // 'branch_code' => $data->store['branch_code'],
            // 'head_of_store' => $data->store['head_of_store'],
        ]);
    }
}

?>