<?php

namespace App\Http\Controllers;

use App\Services\TransactionExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    protected $exportService;
    
    public function __construct(TransactionExportService $exportService)
    {
        $this->exportService = $exportService;
    }
    
    /**
     * Export transactions to Excel - ALL DATA
     */
    public function transactionsXlsx(Request $request)
    {
        return $this->exportService->exportToExcel($request);
    }
    
    /**
     * Export transactions to PDF - ALL DATA
     */
    public function transactionsPdf(Request $request)
    {
        return $this->exportService->exportToPdf($request);
    }
}