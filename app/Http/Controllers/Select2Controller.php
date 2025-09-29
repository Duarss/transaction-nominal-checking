<?php

namespace App\Http\Controllers;

use App\Helper\Select2;
use Illuminate\Http\Request;

class Select2Controller extends Controller
{
    public function filterTransactionsBranchAdmin() 
    {
        return Select2::filterTransactionsBranchAdmin();
    }

    public function filterTransactionsCompanyAdmin()
    {
        return Select2::filterTransactionsCompanyAdmin();
    }
}
