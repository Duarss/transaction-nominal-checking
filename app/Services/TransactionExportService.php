<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Branch;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;

class TransactionExportService
{
    /**
     * Parse date range from request
     */
    public function parseRange($dateRange)
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
     * Build query for transactions with filters
     */
    private function buildQuery($startDate = null, $endDate = null, $method = null, $adminBranchCode = null, $searchValue = null)
    {
        $query = Transaction::with(['branch', 'customer', 'detailTransactions'])
            ->withSum('detailTransactions as paid_amount_sum', 'amount')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->when($method !== null && $method !== '', function ($q) use ($method) {
                $q->whereHas('detailTransactions', function ($detailQuery) use ($method) {
                    $detailQuery->where('payment_type', $method);
                });
            })
            ->when($adminBranchCode, function ($q) use ($adminBranchCode) {
                $q->where('branch_code', $adminBranchCode);
            })
            ->when($searchValue, function ($q) use ($searchValue) {
                $q->where(function ($query) use ($searchValue) {
                    $query->where('doc_id', 'like', "%{$searchValue}%")
                          ->orWhere('sales_code', 'like', "%{$searchValue}%")
                          ->orWhereHas('customer', function ($q) use ($searchValue) {
                              $q->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('code', 'like', "%{$searchValue}%");
                          })
                          ->orWhereHas('branch', function ($q) use ($searchValue) {
                              $q->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('code', 'like', "%{$searchValue}%");
                          });
                });
            });
        
        return $query;
    }
    
    /**
     * Get ALL filtered transactions for export (without pagination)
     */
    public function getTransactionsForExport($startDate = null, $endDate = null, $status = null, $method = null, $adminBranchCode = null, $searchValue = null)
    {
        Log::info('getTransactionsForExport called with:', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'method' => $method,
            'adminBranchCode' => $adminBranchCode,
            'searchValue' => $searchValue
        ]);
        
        // Build query dengan semua filter
        $query = $this->buildQuery($startDate, $endDate, $method, $adminBranchCode, $searchValue);
        
        // Get ALL data tanpa pagination
        $transactions = $query->orderBy('date', 'desc')->get();
        
        Log::info('Total transactions found: ' . $transactions->count());
        
        // Process each transaction
        return $transactions->map(function ($transaction) {
            // Get payment types
            $paymentTypes = $transaction->detailTransactions
                ->pluck('payment_type')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
            
            $paidAmount = $transaction->paid_amount_sum ?? 0;
            $discrepancy = $paidAmount - $transaction->total;
            
            return [
                'doc_id' => $transaction->doc_id,
                'date' => $transaction->date,
                'branch_name' => $transaction->branch ? $transaction->branch->name : '-',
                'sales_name' => $transaction->sales_code ?? '-',
                'customer_name' => $transaction->customer ? $transaction->customer->name : '-',
                'total' => $transaction->total,
                'paid_amount' => $paidAmount,
                'payment_types' => !empty($paymentTypes) ? implode(', ', $paymentTypes) : '-',
                'discrepancy' => $discrepancy,
            ];
        });
    }

    /**
     * Apply status filter to already processed transactions
     */
    private function applyStatusFilter($transactions, $status)
    {
        if (!$status) {
            return $transactions;
        }
        
        return $transactions->filter(function ($transaction) use ($status) {
            $discrepancy = $transaction['discrepancy'] ?? 0;
            
            if ($status === 'paid')     return $discrepancy == 0;
            if ($status === 'pending')  return $discrepancy < 0;
            if ($status === 'overpaid') return $discrepancy > 0;
            
            return true;
        });
    }
    
    /**
     * Get transactions for DataTables (with pagination)
     */
    public function getTransactionsForDataTables($startDate = null, $endDate = null, $status = null, $method = null, $adminBranchCode = null)
    {
        // Query dengan pagination untuk DataTables
        return $this->buildQuery($startDate, $endDate, $method, $adminBranchCode);
    }
    
    /**
     * Get admin branch code if user is branch_admin
     */
    public function getAdminBranchCode($user)
    {
        if ($user && $user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            return $branch ? $branch->code : null;
        }
        return null;
    }
    
    /**
     * Get status label for display
     */
    public function getStatusLabel($status)
    {
        $labels = [
            'paid' => 'Sesuai',
            'pending' => 'Kurang Bayar',
            'overpaid' => 'Lebih Bayar',
        ];
        
        return $status ? ($labels[$status] ?? 'Semua Status') : 'Semua Status';
    }
    
    /**
     * Export to Excel
     */
    public function exportToExcel($request)
    {
        Log::info('=== exportToExcel CALLED ===');
        Log::info('Request parameters:', $request->all());
        
        // Get filters from request
        [$start, $end] = $this->parseRange($request->get('date_range'));
        $status = $request->get('status');
        $method = $request->get('method');
        $searchValue = $request->get('search') ? $request->get('search')['value'] : null;
        
        // Apply branch filter for branch_admin
        $adminBranchCode = $this->getAdminBranchCode(auth()->user());
        
        // Get ALL transactions data dengan semua filter
        $transactions = $this->getTransactionsForExport(
            $start, $end, null, $method, $adminBranchCode, $searchValue
        );
        
        // Apply status filter setelah data diambil
        if ($status) {
            $transactions = $this->applyStatusFilter($transactions, $status);
        }
        
        Log::info('Exporting Excel - Total records after filter: ' . $transactions->count());
        
        // Generate filename
        $filename = 'transaksi-' . now()->format('Y-m-d-H-i') . '.xlsx';
        
        // Return Excel download
        return Excel::download(
            new TransactionsExport($transactions), 
            $filename
        );
    }
    
    /**
     * Export to PDF
     */
    public function exportToPdf($request)
    {
        Log::info('=== exportToPdf CALLED ===');
        
        $user = auth()->user();
        
        // Get filters from request
        [$start, $end] = $this->parseRange($request->get('date_range'));
        $status = $request->get('status');
        $method = $request->get('method');
        $searchValue = $request->get('search') ? $request->get('search')['value'] : null;
        
        // Apply branch filter for branch_admin
        $adminBranchCode = $this->getAdminBranchCode($user);
        
        // Get ALL transactions data dengan semua filter
        $transactions = $this->getTransactionsForExport(
            $start, $end, null, $method, $adminBranchCode, $searchValue
        );
        
        // Apply status filter setelah data diambil
        if ($status) {
            $transactions = $this->applyStatusFilter($transactions, $status);
        }
        
        Log::info('Exporting PDF - Total records: ' . $transactions->count());
        
        // Format untuk PDF
        $formattedData = $this->formatForPdf($transactions);
        $totals = $this->calculateTotals($transactions);
        
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
            'totals' => $totals
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
    
    /**
     * Format data specifically for PDF
     */
    private function formatForPdf($transactions)
    {
        return $transactions->map(function ($transaction) {
            return [
                'doc_id' => $transaction['doc_id'],
                'date' => Carbon::parse($transaction['date'])->format('d M Y'),
                'branch_name' => $transaction['branch_name'],
                'sales_name' => $transaction['sales_name'],
                'customer_name' => $transaction['customer_name'],
                'total' => $transaction['total'],
                'paid_amount' => $transaction['paid_amount'],
                'discrepancy' => $transaction['discrepancy'],
                'method' => $transaction['payment_types'],
                'status' => $transaction['discrepancy'] == 0 ? 'SESUAI' : 
                          ($transaction['discrepancy'] > 0 ? 'LEBIH' : 'KURANG'),
            ];
        });
    }
    
    /**
     * Calculate totals
     */
    private function calculateTotals($transactions)
    {
        return [
            'total' => $transactions->sum('total'),
            'paid' => $transactions->sum('paid_amount'),
            'discrepancy' => $transactions->sum('discrepancy'),
        ];
    }
}