<?php

namespace Database\Seeders;

use App\Models\ActionLog;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActionLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 30;
        $transactions = Transaction::all();
        $statuses = ['updated', 'approved', 'rechecked'];
        foreach (range(1, $count) as $i) {
            $trx = $transactions->random();

            $branch = Branch::where('code', $trx->branch_code)->first();
            
            $adminUser = null;
            if ($branch && $branch->branch_admin) {
                $adminUser = User::where('code', $branch->branch_admin)->first();
            }

            if (!$adminUser) {
                continue; // Skip if no admin user found
            }

            $status = $statuses[array_rand($statuses)];
            $nominal_before = $trx->total;
            // For demonstration: if updated, set nominal_after to a different value, else set to the same value
            $nominal_after = $status === 'updated' ? ($nominal_before + rand(-10000, 10000)) : $nominal_before;
            ActionLog::create([
                'transaction_code' => $trx->doc_id,
                'nominal_before' => $nominal_before,
                'nominal_after' => $nominal_after,
                'status' => $status,
                'done_by' => $adminUser->username,
            ]);
        }
    }
}
