<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $transactions;
    
    /**
     * Constructor - menerima data yang sudah diformat dari Service
     */
    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }
    
    public function collection()
    {
        return $this->transactions;
    }
    
    public function headings(): array
    {
        return [
            'Doc ID',
            'Tanggal',
            'Cabang',
            'Kode Sales',
            'Pelanggan',
            'Total',
            'Terbayar',
            'Selisih',
            'Status',
            'Metode Pembayaran'
        ];
    }
    
    public function map($transaction): array
    {
        $discrepancy = $transaction['discrepancy'] ?? 0;
        $status = $discrepancy == 0 ? 'SESUAI' : 
                 ($discrepancy > 0 ? 'LEBIH' : 'KURANG');
        
        return [
            $transaction['doc_id'] ?? '-',
            isset($transaction['date']) ? Carbon::parse($transaction['date'])->format('d M Y') : '-',
            $transaction['branch_name'] ?? '-',
            $transaction['sales_name'] ?? '-',
            $transaction['customer_name'] ?? '-',
            $transaction['total'] ?? 0,
            $transaction['paid_amount'] ?? 0,
            $discrepancy,
            $status,
            $transaction['payment_types'] ?? '-',
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}