<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Branch;
use App\Models\DetailTransaction;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\TransactionExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DatatableController extends Controller
{
    protected $exportService;
    
    public function __construct(TransactionExportService $exportService)
    {
        $this->exportService = $exportService;
    }
    
    public function transactionNominalList(Request $request)
    {
        $user = auth()->user();
        
        // Get filters
        $status = $request->get('status');
        $method = $request->get('method');
        
        // Parse date range jika ada
        $dateRange = $request->get('date_range');
        [$start, $end] = $this->exportService->parseRange($dateRange);
        
        // Apply branch filter for branch_admin
        $adminBranchCode = $this->exportService->getAdminBranchCode($user);
        
        // Gunakan service untuk mendapatkan query
        $query = $this->exportService->getTransactionsForDataTables($start, $end, $status, $method, $adminBranchCode);
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('method', function($row) {
                $paymentTypes = $row->detailTransactions
                    ->pluck('payment_type')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();
                
                return !empty($paymentTypes) ? implode(', ', $paymentTypes) : '-';
            })
            ->addColumn('sales_name', function($row) {
                return $row->sales_code ?? '-';
            })
            ->addColumn('customer_name', function($row) {
                return $row->customer ? $row->customer->name : '-';
            })
            ->addColumn('branch_name', function($row) {
                return $row->branch ? $row->branch->name : '-';
            })
            ->addColumn('discrepancy', function($row) {
                $discrepancy = $row->paid_amount_sum - $row->total;
                return number_format($discrepancy, 0, ',', '.');
            })
            ->addColumn('status_badge', function($row) {
                $discrepancy = $row->paid_amount_sum - $row->total;
                if ($discrepancy == 0) {
                    return '<span class="badge bg-success">SESUAI</span>';
                } elseif ($discrepancy > 0) {
                    return '<span class="badge bg-warning">LEBIH</span>';
                } else {
                    return '<span class="badge bg-danger">KURANG</span>';
                }
            })
            ->addColumn('total_formatted', function($row) {
                return 'IDR ' . number_format($row->total, 0, ',', '.');
            })
            ->addColumn('paid_amount_formatted', function($row) {
                return 'IDR ' . number_format($row->paid_amount_sum, 0, ',', '.');
            })
            ->addColumn('date_formatted', function($row) {
                return \Carbon\Carbon::parse($row->date)->format('d M Y');
            })
            ->orderColumn('date', 'date $1') // Fix untuk SQL Server
            ->rawColumns(['status_badge', 'method', 'action'])
            ->toJson();
    }
    
    public function masterBranch(Request $request)
    {
        $query = Branch::with(['stores', 'branchAdmin']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('branch', function($row) {
                return $row->code ? $row->code . ' - ' . $row->name : '';
            })
            ->addColumn('address', function($row) {
                return $row->address ?? '';
            })
            ->addColumn('stores_count', function($row) {
                return $row->stores ? $row->stores->count() : 0;
            })
            ->addColumn('branch_admin', function($row) {
                return $row->branchAdmin ? $row->branchAdmin->code : '';
            })
            ->addColumn('created_at', function($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '';
            })
            ->addColumn('updated_at', function($row) {
                return $row->updated_at ? $row->updated_at->format('d M Y H:i') : '';
            })
            ->addColumn('details', function($model) {
                return view('datatables.details-master-branch', compact('model'))->render();
            })
            ->orderColumn('created_at', 'created_at $1') // Fix untuk SQL Server
            ->rawColumns(['details'])
            ->toJson();
    }

    public function detailMasterBranch(Request $request)
    {
        $branchCode = $request->get('code');
        $query = Store::with(['branch'])
            ->where('branch_code', $branchCode);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('store', function($row) {
                return $row->code ? $row->code . ' - ' . $row->name : '';
            })
            ->addColumn('address', function($row) {
                return $row->address ?? '';
            })
            ->addColumn('created_at', function($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '';
            })
            ->addColumn('updated_at', function($row) {
                return $row->updated_at ? $row->updated_at->format('d M Y H:i') : '';
            })
            ->toJson();
    }

    public function masterStore(Request $request)
    {
        $user = auth()->user();

        $query = Store::with(['branch']);

        if ($user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            if ($branch) {
                $query->where('branch_code', $branch->code);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('store', function($row) {
                return $row->code ? $row->code . ' - ' . $row->name : '';
            })
            ->addColumn('address', function($row) {
                return $row->address ?? '';
            })
            ->addColumn('branch', function($row) {
                return $row->branch ? $row->branch->code . ' - ' . $row->branch->name : '';
            })
            ->addColumn('created_at', function($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '';
            })
            ->addColumn('updated_at', function($row) {
                return $row->updated_at ? $row->updated_at->format('d M Y H:i') : '';
            })
            ->addColumn('action', function($model) {
                return view('datatables.action-master-store', compact('model'))->render();
            })
            ->orderColumn('created_at', 'created_at $1') // Fix untuk SQL Server
            ->rawColumns(['action'])
            ->toJson();
    }

    public function masterTransaction(Request $request)
    {
        $user = auth()->user();
        $isBranchAdmin = $user->role === 'branch_admin';
        $isCompanyAdmin = $user->role === 'company_admin';

        // Tambah withSum untuk paid_amount_sum
        $query = Transaction::with(['branch', 'customer', 'detailTransactions'])
            ->withSum('detailTransactions as paid_amount_sum', 'amount')
            ->when($isCompanyAdmin, function ($q) use ($request) {
                $rechecked = $request->input('recheck');
                if ($rechecked === 'true') {
                    $q->where('is_rechecked', 1);
                } else if ($rechecked === 'all') {
                    $q->whereIn('is_rechecked', [0, 1])
                        ->where('is_approved', 1);
                } else {
                    $q->where('is_rechecked', 0)
                        ->where('is_approved', 1);
                }
            })
            ->when($isBranchAdmin, function ($q) use ($user, $request) {
                $branch = Branch::where('branch_admin', $user->code)->first();
                if ($branch) {
                    $q->where('branch_code', $branch->code);
                } else {
                    $q->whereRaw('1=0');
                }
                
                $approval = $request->input('approval');
                if ($approval === 'true') {
                    $q->where('is_approved', 1);
                } else if ($approval === 'false') {
                    $q->where('is_approved', 0);
                } else if ($approval === 'rechecked') {
                    $q->where('is_rechecked', 1);
                }
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('doc_id', fn($row) => $row->doc_id)
            ->addColumn('date', fn($row) => $row->date ? Carbon::parse($row->date)->format('d M Y') : '')
            ->addColumn('sales_code', fn($row) => $row->sales_code ?? '-')
            ->addColumn('customer_name', fn($row) => $row->customer?->name ?? '-')
            ->addColumn('branch_name', fn($row) => $row->branch?->name ?? '-')
            ->addColumn('total', fn($row) => $row->total ?? '-')
            ->addColumn('paid_amount', fn($row) => $row->paid_amount ?? '-')
            ->addColumn('paid_amount_sum', fn($row) => $row->paid_amount_sum ?? '-')
            ->addColumn('discrepancy', function($row) {
                $discrepancy = ($row->paid_amount_sum ?? 0) - ($row->total ?? 0);
                $formatted = 'IDR ' . number_format(abs($discrepancy), 0, ',', '.');
                $badge = $discrepancy == 0 ? 'success' : ($discrepancy > 0 ? 'warning' : 'danger');
                return '<span class="badge bg-' . $badge . '">' . $formatted . '</span>';
            })
            ->addColumn('created_by', fn($row) => $row->created_by ?? '-')
            ->addColumn('updated_by', fn($row) => $row->updated_by ?? '-')
            ->addColumn('is_approved', fn($row) => (bool) $row->is_approved)
            ->addColumn('payment_types', function($row) {
                $paymentTypes = $row->detailTransactions
                    ->pluck('payment_type')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray();
                return !empty($paymentTypes) ? implode(', ', $paymentTypes) : '-';
            })
            ->addColumn('details', function ($model) {
                return view('datatables.details-master-transaction', compact('model'))->render();
            })
            ->addColumn('approve', function ($model) {
                if (!$model->is_approved) {
                    return view('datatables.approve-master-transaction', compact('model'))->render();
                }
                if ($model->is_rechecked) {
                    return '<span class="badge bg-secondary">RECHECKED</span>';
                }
                return '<span class="badge bg-success">APPROVED</span>';
            })
            ->addColumn('checklist', function ($model) use ($isCompanyAdmin) {
                if ($isCompanyAdmin && $model->is_approved) {
                    return view('datatables.checklist-master-transaction', compact('model'))->render();
                }
                return '';
            })
            ->orderColumn('date', 'date $1') // Fix untuk SQL Server
            ->rawColumns(['discrepancy', 'details', 'approve', 'checklist', 'payment_types'])
            ->toJson();
    }

    public function detailMasterTransaction(Request $request)
    {
        $docId = $request->get('doc_id');
        $query = DetailTransaction::with(['transaction'])
            ->where('doc_id', $docId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('doc_id', function($row) {
                return $row->transaction->doc_id;
            })
            ->addColumn('item_index', function($row) {
                return $row->item_index;
            })
            ->addColumn('payment_type', function($row) {
                return $row->payment_type ? $row->payment_type : '-';
            })
            ->addColumn('amount', function($row) {
                return 'IDR ' . number_format($row->amount, 0, ',', '.');
            })
            ->addColumn('bank', function($row) {
                return $row->bank ? $row->bank : '-';
            })
            ->addColumn('bank_doc', function($row) {
                return $row->bank_doc ? $row->bank_doc : '-';
            })
            ->addColumn('bank_due', function($row) {
                return $row->bank_due ? \Carbon\Carbon::parse($row->bank_due)->format('d M Y') : '-';
            })
            ->addColumn('location', function($row) {
                return $row->location ? $row->location : '-';
            })
            ->addColumn('created_at', function($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->toJson();
    }

    public function actionLog(Request $request)
    {
        $user = auth()->user();

        $query = ActionLog::with(['transaction', 'user']);

        if ($user && $user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            if ($branch) {
                $branchCode = $branch->code;
                $query->whereHas('transaction', function($q) use ($branchCode) {
                    $q->where('branch_code', $branchCode);
                });
            } else {
                $query->whereRaw('1=0');
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('transaction_code', function($row) {
                return $row->transaction ? $row->transaction->doc_id : '-';
            })
            ->addColumn('nominal_before', function($row) {
                // Ambil dari transaction->total jika ada, jika tidak dari nominal_before
                return $row->transaction ? $row->transaction->total : '-';
            })
            ->addColumn('nominal_after', function($row) {
                // Pastikan nominal_after valid dan numerik
                return $row->nominal_after ?? '-';
            })
            ->addColumn('status', function($row) {
                return $row->status ?? '-';
            })
            ->addColumn('done_by', function($row) {
                return $row->user ? $row->user->username : '-';
            })
            ->addColumn('created_at', function($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->orderColumn('created_at', 'created_at $1')
            ->rawColumns(['status'])
            ->toJson();
    }
}