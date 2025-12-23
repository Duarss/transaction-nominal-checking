<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Branch;
use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Parse date range from request
     */
    private function parseRange($dateRange)
    {
        if (!$dateRange) {
            return [null, null];
        }
        
        $dates = explode(' - ', $dateRange);
        if (count($dates) !== 2) {
            return [null, null];
        }
        
        return [
            Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay(),
            Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay()
        ];
    }
    
    /**
     * Export transactions to Excel
     */
    public function transactionsXlsx(Request $request)
    {
        // Get filters from request
        [$start, $end] = $this->parseRange($request->get('date_range'));
        $status = $request->get('status');
        $method = $request->get('method');

        // Generate filename with correct date format
        $filename = 'transaksi-' . now()->format('Y-m-d-H-i') . '.xlsx';

        // Return Excel download
        return Excel::download(
            new TransactionsExport($start, $end, $status, $method), 
            $filename
        );
    }

    public function transactionsPdf(Request $request)
    {
        $user = auth()->user();
        
        // Get filters from request
        [$start, $end] = $this->parseRange($request->get('date_range'));
        $status = $request->get('status');
        $method = $request->get('method');
        
        // Apply branch filter for branch_admin
        $adminBranchCode = null;
        if ($user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            if ($branch) {
                $adminBranchCode = $branch->code;
            }
        }
        
        // Using Eloquent with relationships
        $query = Transaction::with(['branch', 'customer', 'sales'])
            ->withSum('detailTransactions as paid_amount_sum', 'amount')
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
            })
            ->when($method !== null && $method !== '', function ($q) use ($method) {
                $q->whereHas('detailTransactions', function ($detailQuery) use ($method) {
                    $detailQuery->where('payment_type', $method);
                });
            })
            ->when($adminBranchCode, function ($q) use ($adminBranchCode) {
                $q->where('branch_code', $adminBranchCode);
            })
            ->orderBy('date', 'desc');
        
        // Get all transactions
        $transactions = $query->get();
        
        // Process each transaction to add payment types and discrepancy
        $transactions = $transactions->map(function ($transaction) {
            // Get payment types from detail transactions
            $paymentTypes = $transaction->detailTransactions
                ->pluck('payment_type')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
            
            $transaction->payment_types = !empty($paymentTypes) ? implode(', ', $paymentTypes) : '-';
            $transaction->discrepancy = $transaction->paid_amount_sum - $transaction->total;
            
            return $transaction;
        });
        
        // Apply status filter in PHP
        if ($status) {
            $transactions = $transactions->filter(function ($transaction) use ($status) {
                if ($status === 'paid')     return $transaction->discrepancy == 0;
                if ($status === 'pending')  return $transaction->discrepancy < 0;
                if ($status === 'overpaid') return $transaction->discrepancy > 0;
                return true;
            });
        }
        
        // Format data for PDF
        $formattedData = $transactions->map(function ($transaction) {
            return [
                'doc_id' => $transaction->doc_id,
                'date' => Carbon::parse($transaction->date)->format('d M Y'),
                'branch_name' => $transaction->branch ? $transaction->branch->name : '-',
                'sales_name' => $transaction->sales ? $transaction->sales->name : '-',
                'customer_name' => $transaction->customer ? $transaction->customer->name : '-',
                'total' => $transaction->total,
                'paid_amount' => $transaction->paid_amount_sum,
                'discrepancy' => $transaction->discrepancy,
                'method' => $transaction->payment_types,
                'status' => $transaction->discrepancy == 0 ? 'SESUAI' : 
                        ($transaction->discrepancy > 0 ? 'LEBIH' : 'KURANG'),
            ];
        });
        
        // Calculate totals
        $totalSum = $formattedData->sum('total');
        $paidSum = $formattedData->sum('paid_amount');
        $discrepancySum = $formattedData->sum('discrepancy');
        
        // Generate HTML for PDF
        $html = view('exports.transactions-pdf', [
            'data' => $formattedData,
            'title' => 'Laporan Transaksi',
            'exportDate' => now()->translatedFormat('d F Y H:i'),
            'user' => $user,
            'filters' => [
                'date_range' => $request->get('date_range') ?: 'Semua Tanggal',
                'status' => $this->getStatusLabel($status),
                'method' => $method ?: 'Semua Metode',
            ],
            'totals' => [
                'total' => $totalSum,
                'paid' => $paidSum,
                'discrepancy' => $discrepancySum,
            ]
        ])->render();
        
        // Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $filename = 'transaksi-' . now()->format('Y-m-d-H-i') . '.pdf';
        
        // Return the PDF as download
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    private function getStatusLabel($status)
    {
        $labels = [
            'paid' => 'Sesuai',
            'pending' => 'Kurang Bayar',
            'overpaid' => 'Lebih Bayar',
        ];
        
        return $status ? ($labels[$status] ?? 'Semua Status') : 'Semua Status';
    }

    // private function parseRange(?string $range): array
    // {
    //     if (!$range) return [null, null];
    //     $parts = preg_split('/\s*-\s*/', $range);
    //     if (count($parts) !== 2) return [null, null];
    //     try {
    //         return [now()->parse($parts[0])->startOfDay(), now()->parse($parts[1])->endOfDay()];
    //     } catch (\Throwable $e) {
    //         return [null, null];
    //     }
    // }
}