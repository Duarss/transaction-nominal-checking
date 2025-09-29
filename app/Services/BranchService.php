<?php

namespace App\Services;

use App\Models\Branch;

class BranchService
{
    public function fetch(array $request) : object
    {
        return (object) [
            'branch' => [
                'code' => $request['code'],
                'name' => $request['name'] ?? null,
                'address' => $request['address'] ?? null,
            ],
            'store' => [
                'code' => $request['code'],
                'name' => $request['name'] ?? null,
                'address' => $request['address'] ?? null,
                // 'branch_code' => $request['branch_code'] ?? null,
                // 'head_of_store' => $request['head_of_store'] ?? null,
            ],
            'user' => [
                'username' => $request['username'] ?? null,
                'name' => $request['name'] ?? null,
                'email' => $request['email'] ?? null,
                'role' => $request['role'] ?? null,
            ],
        ];
    }

    public function update(Branch $branch, array $request)
    {
        $data = $this->fetch($request);

        return $branch->update([
            // 'code' => $data->branch['code'], // Code should not be updated
            'name' => $data->branch['name'],
            'address' => $data->branch['address'],
        ]);
    }
}

?>