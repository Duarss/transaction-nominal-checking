<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardDataController extends Controller
{
    public function summary(Request $request)
    {
        $status   = $request->get('status');     // 'equal'|'underpaid'|'overpaid'|''

        $prefix = DB::getTablePrefix();
        $T  = $prefix . 't';

        $user = auth()->user();
        $adminBranchCode = null;
        if ($user && $user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            if ($branch) {
                $adminBranchCode = $branch->code;
            } else {
                return response()->json([
                    'total_amount' => 0.0,
                    'discrepancies' => 0,
                    'today_count' => 0,
                ]);
            }
        }

        $base = DB::table('transactions as t')
            // No need to join detail_transactions for paid_amount
            ->when($adminBranchCode, fn($q) => $q->where('t.branch_code', $adminBranchCode))
            ->select([
                DB::raw("[$T].[doc_id] as doc_id"),
                DB::raw("[$T].[total]  as total"),
                DB::raw("[$T].[date]   as [date]"),
                DB::raw("[$T].[paid_amount] as paid_amount"),
        ]);

        $agg = DB::query()->fromSub($base, 'x');
        if ($status) {
            $agg->where(function($w) use ($status) {
                if ($status === 'equal')     $w->whereRaw('paid_amount = total');
                if ($status === 'underpaid')  $w->whereRaw('paid_amount < total');
                if ($status === 'overpaid') $w->whereRaw('paid_amount > total');
            });
        }

        $rows = $agg->get();

        $total_amount = (float) $rows->sum('total');
        $discrepancies = $rows->filter(fn($r) => $r->paid_amount != $r->total)->count();
        $today_count  = $rows->filter(fn($r) => Carbon::parse($r->date)->isToday())->count();

        return response()->json([
            'total_amount' => $total_amount,
            'discrepancies' => $discrepancies,
            'today_count' => $today_count,
        ]);
    }
}
