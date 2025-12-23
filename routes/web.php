<?php

use App\Http\Controllers\ActionLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\DatatableController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransactionController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/main/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/main/dashboard/summary', [DashboardDataController::class, 'summary'])->name('dashboard.summary');

    Route::apiResource('main/actionLog', ActionLogController::class)
        ->parameters(['main-actionLog' => 'mainActionLog'])
        ->names('mainActivityLog');

    Route::apiResource('master/branch', BranchController::class)
        ->parameters(['master-branch' => 'masterBranch'])
        ->names('masterBranch');

    Route::apiResource('master/store', StoreController::class)
        ->parameters(['master-store' => 'masterStore'])
        ->names('masterStore');

    Route::apiResource('master/transaction', TransactionController::class)
        ->parameters(['master-transaction' => 'masterTransaction'])
        ->names('masterTransaction');

    // API route access
    Route::get('api/transactions/{doc_id}/details', [TransactionController::class, 'getDetailsJson'])->middleware(['auth']);

    // Approve trx based on user's input
    Route::post('master/transaction/{transaction}/approve', [TransactionController::class, 'approve'])
        ->name('masterTransaction.approve');
    
    // Recheck approved trx
    Route::post('master/transaction/{transaction}/recheck', [TransactionController::class, 'recheck'])
        ->name('masterTransaction.recheck');

    // Unrecheck rechecked trx
    Route::post('master/transaction/{transaction}/unrecheck', [TransactionController::class, 'unrecheck'])
        ->name('masterTransaction.unrecheck');

    // Detail page routes
    // master-branch
    Route::get('master/branch/{branch}/details', [BranchController::class, 'viewDetails'])
        ->name('masterBranch.details');
    // master-transaction
    Route::get('master/transaction/{transaction}/details', [TransactionController::class, 'viewDetails'])
        ->name('masterTransaction.details');

    Route::group(['prefix' => 'datatables', 'as' => 'datatables.'], function () {
        Route::match(['GET','POST'], '/transactions/nominal/list', [DatatableController::class, 'transactionNominalList'])
            ->name('transactions.nominal.list');
        Route::get('/transactions/export/xlsx',   [ExportController::class, 'transactionsXlsx'])
            ->name('transactions.export.xlsx');
        Route::get('/transactions/export/pdf', [ExportController::class, 'transactionsPdf'])
            ->name('transactions.export.pdf');
        Route::post('main-action-log', [DatatableController::class, 'actionLog'])->name('main-action-log');
        Route::post('master-branch', [DatatableController::class, 'masterBranch'])->name('master-branch');
        Route::post('master-store', [DatatableController::class, 'masterStore'])->name('master-store');
        Route::post('master-transaction', [DatatableController::class, 'masterTransaction'])->name('master-transaction');
        Route::post('detail-master-branch', [DatatableController::class, 'detailMasterBranch'])->name('detail-master-branch');
        Route::post('detail-master-transaction', [DatatableController::class, 'detailMasterTransaction'])->name('detail-master-transaction');
    });

    Route::group(['prefix' => 'select2', 'as' => 'select2.'], function () {
        Route::post('/filter-transactions-branch-admin', [Select2Controller::class, 'filterTransactionsBranchAdmin'])->name('filter-transactions-branch-admin');
        Route::post('/filter-transactions-company-admin', [Select2Controller::class, 'filterTransactionsCompanyAdmin'])->name('filter-transactions-company-admin');
    });
});
