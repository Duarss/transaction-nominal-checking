<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $storesPerBranch = 5; // or any number you want per branch
        $branches = Branch::all();

        foreach ($branches as $branch) {
            for ($i = 1; $i <= $storesPerBranch; $i++) {
                Store::create([
                    'code' => $branch->code . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'Store ' . $i,
                    'address' => fake()->address(),
                    'branch_code' => $branch->code,
                ]);
            }
        }
    }
}
