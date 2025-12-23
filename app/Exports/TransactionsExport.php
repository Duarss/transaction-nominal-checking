<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $status;
    protected $method;

    public function __construct($startDate = null, $endDate = null, $status = null, $method = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->method = $method;
    }

    public function collection()
    {
        $query = DB::table('transactions as t')
            ->leftJoin('detail_transactions as dt', 'dt.doc_id', '=', 't.doc_id')
            ->leftJoin('branches as b', 'b.code', '=', 't.branch_code')
            ->leftJoin('users as u', 'u.code', '=', 't.sales_code')
            ->leftJoin('stores as s', 's.code', '=', 't.customer_code')
            ->when($this->startDate && $this->endDate, function($q) {
                return $q->whereBetween('t.date', [$this->startDate, $this->endDate]);
            })
            ->when($this->method !== null && $this->method !== '', function($q) {
                return $q->where('dt.payment_type', $this->method);
            })
            ->groupBy('t.doc_id', 't.date', 't.total', 'b.name', 'u.name', 's.name')
            ->selectRaw("
                t.doc_id, 
                t.date, 
                t.total,
                b.name as branch_name, 
                u.name as sales_name, 
                s.name as customer_name,
                COALESCE(SUM(dt.amount), 0) as paid_amount,
                GROUP_CONCAT(DISTINCT dt.payment_type ORDER BY dt.payment_type SEPARATOR ', ') as payment_types
            ");

        // Apply status filter if provided
        if ($this->status) {
            $query = DB::query()->fromSub($query, 'x')->selectRaw("*, (paid_amount - total) as discrepancy");
            
            $query->where(function($w) {
                if ($this->status === 'paid')     $w->whereRaw('paid_amount = total');
                if ($this->status === 'pending')  $w->whereRaw('paid_amount < total');
                if ($this->status === 'overpaid') $w->whereRaw('paid_amount > total');
            });
        } else {
            $query = DB::query()->fromSub($query, 'x')->selectRaw("*, (paid_amount - total) as discrepancy");
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Doc ID',
            'Tanggal',
            'Cabang',
            'Sales',
            'Pelanggan',
            'Total',
            'Terbayar',
            'Selisih',
            'Metode Pembayaran'
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->doc_id,
            $transaction->date,
            $transaction->branch_name,
            $transaction->sales_name,
            $transaction->customer_name,
            $transaction->total,
            $transaction->paid_amount,
            $transaction->discrepancy,
            $transaction->payment_types,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Optional: Format number columns
            'F' => ['numberFormat' => '#,##0.00'],
            'G' => ['numberFormat' => '#,##0.00'],
            'H' => ['numberFormat' => '#,##0.00'],
        ];
    }
}