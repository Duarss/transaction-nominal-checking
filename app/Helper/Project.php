<?php

namespace App\Helper;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class Project
{
    public static function filterTransactionsBranchAdmin()
    {
        return response()->json([
            ['id' => 'false', 'text' => 'Belum Disetujui'],
            ['id' => 'true',  'text' => 'Sudah Disetujui'],
            ['id' => 'rechecked', 'text' => 'Sudah Dicek Ulang'],
            ['id' => 'all',   'text' => 'Semua Transaksi'],
        ]);
    }

    public static function filterTransactionsCompanyAdmin()
    {
        return response()->json([
            ['id' => 'false', 'text' => 'Belum Dicek Ulang'],
            ['id' => 'true',  'text' => 'Sudah Dicek Ulang'],
            ['id' => 'all',   'text' => 'Semua Transaksi'],
        ]);
    }
}

?>