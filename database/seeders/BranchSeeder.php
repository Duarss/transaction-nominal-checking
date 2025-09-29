<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 10;
        $users = User::where('role', 'branch_admin')->pluck('code')->shuffle()->values()->toArray();
        if (count($users) < $count) {
            throw new \Exception('Not enough branch_admin users for branches');
        }
        $branchCodes = [];
        foreach (range(1, $count) as $i) {
            // $now = now();
            // Generate a unique branch code prefix, e.g., BRX + 4 random uppercase letters
            do {
                $prefix = 'BRX' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            } while (in_array($prefix, $branchCodes));
            $branchCodes[] = $prefix;

            Branch::create([
                'code' => $prefix,
                'name' => 'Branch ' . $i,
                'address' => fake()->address(),
                'branch_admin' => $users[$i - 1],
            ]);
        }
    }
}
