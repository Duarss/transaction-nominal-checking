<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\ActionLog;
use App\Models\Branch;
use App\Models\DetailTransaction;
use App\Models\Store;
use App\Models\Transaction;
use App\Policies\ActionLogPolicy;
use App\Policies\BranchPolicy;
use App\Policies\DetailTransactionPolicy;
use App\Policies\StorePolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Branch::class => BranchPolicy::class,
        Store::class => StorePolicy::class,
        Transaction::class => TransactionPolicy::class,
        DetailTransaction::class => DetailTransactionPolicy::class,
        ActionLog::class => ActionLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
