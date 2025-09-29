<?php

namespace App\Helper;

class Select2
{
    public static function filterTransactionsBranchAdmin() 
    {
        return Project::filterTransactionsBranchAdmin();
    }

    public static function filterTransactionsCompanyAdmin()
    {
        return Project::filterTransactionsCompanyAdmin();
    }
}

?>