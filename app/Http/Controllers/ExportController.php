<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function transactionsCsv(Request $request): StreamedResponse
    {
        [$start, $end] = $this->parseRange($request->get('date_range'));
        $status   = $request->get('status');
        $method   = $request->get('method');

        $sub = DB::table('transactions as t')
            ->leftJoin('detail_transactions as dt', 'dt.doc_id', '=', 't.doc_id')
            ->leftJoin('branches as b', 'b.code', '=', 't.branch_code')
            ->leftJoin('users as u', 'u.code', '=', 't.sales_code')
            ->leftJoin('stores as s', 's.code', '=', 't.customer_code')
            ->when($start && $end, fn($qq) => $qq->whereBetween('t.date', [$start, $end]))
            ->when($method !== null && $method !== '', fn($qq) => $qq->where('dt.payment_type', $method))
            ->groupBy('t.doc_id','t.date','t.total','b.name','u.name','s.name')
            ->selectRaw("
                t.doc_id, t.date, t.total,
                b.name as branch_name, u.name as sales_name, s.name as customer_name,
                COALESCE(SUM(dt.amount),0) as paid_amount,
                GROUP_CONCAT(DISTINCT dt.payment_type ORDER BY dt.payment_type SEPARATOR ', ') as payment_types
            ");

        if ($status) {
            $sub = DB::query()->fromSub($sub, 'x')->where(function($w) use ($status) {
                if ($status === 'paid')     $w->whereRaw('paid_amount = total');
                if ($status === 'pending')  $w->whereRaw('paid_amount < total');
                if ($status === 'overpaid') $w->whereRaw('paid_amount > total');
            });
        } else {
            $sub = DB::query()->fromSub($sub, 'x');
        }

        $rows = $sub->selectRaw("(paid_amount - total) as discrepancy")->orderBy('date','desc')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
        ];

        return response()->stream(function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['doc_id','date','branch','sales','customer','total','paid_amount','discrepancy','payment_types']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->doc_id,
                    $r->date,
                    $r->branch_name,
                    $r->sales_name,
                    $r->customer_name,
                    $r->total,
                    $r->paid_amount,
                    $r->discrepancy,
                    $r->payment_types,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    private function parseRange(?string $range): array
    {
        if (!$range) return [null, null];
        $parts = preg_split('/\s*-\s*/', $range);
        if (count($parts) !== 2) return [null, null];
        try {
            return [now()->parse($parts[0])->startOfDay(), now()->parse($parts[1])->endOfDay()];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}
?>