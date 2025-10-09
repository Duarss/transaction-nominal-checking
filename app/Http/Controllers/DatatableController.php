<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Branch;
use App\Models\DetailTransaction;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DatatableController extends Controller
{
    public function transactionNominalList(Request $request)
    {
        $status   = $request->get('status');     // 'paid'|'pending'|'overpaid'|''
        $method   = $request->get('method');     // detail_transactions.payment_type

        // Make raw parts match SQL Server’s actual (prefixed) aliases
        $prefix = DB::getTablePrefix();  // e.g. 'dms_'
        $T  = $prefix . 't';            // 'dms_t'
        $DT = $prefix . 'dt';           // 'dms_dt'
        $B  = $prefix . 'b';             // branches alias becomes [dms_b]
        $U  = $prefix . 'u';             // users alias becomes [dms_u]
        $S  = $prefix . 's';             // stores alias becomes [dms_s]

        $user = auth()->user();

        // Find branch handled by this admin
        $adminBranchCode = null;
        if ($user && $user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            if ($branch) {
                $adminBranchCode = $branch->code;
            } else {
                // No branch assigned, return empty
                return DataTables::of(collect([]))->toJson();
            }
        }

        // Aggregate per document
        $sub = DB::table('transactions as t')
            ->leftJoin('detail_transactions as dt', 'dt.doc_id', '=', 't.doc_id')
            ->leftJoin('branches as b', 'b.code', '=', 't.branch_code')
            ->leftJoin('stores as s',   's.code', '=', 't.customer_code')
            ->when($method !== null && $method !== '', fn($q) => $q->where('dt.payment_type', $method))
            ->when($adminBranchCode, fn($q) => $q->where('t.branch_code', $adminBranchCode))
            ->groupBy(
                DB::raw("[$T].[doc_id]"),
                DB::raw("[$T].[date]"),
                DB::raw("[$T].[total]"),
                DB::raw("[$B].[name]"),
                DB::raw("[$T].[sales_code]"),
                DB::raw("[$S].[name]"),
                DB::raw("[$T].[branch_code]"),
                DB::raw("[$T].[sales_code]"),
                DB::raw("[$T].[customer_code]"),
                DB::raw("[$T].[paid_amount]"),
            )
            ->selectRaw("
                [$T].[doc_id] as doc_id,
                [$T].[date] as date,
                [$T].[total] as total,
                [$B].[name] as branch_name,
                [$T].[sales_code] as sales_name,
                [$S].[name] as customer_name,
                [$T].[branch_code], [$T].[sales_code], [$T].[customer_code],
                [$T].[paid_amount] as paid_amount,
                STRING_AGG(CAST([$DT].[payment_type] AS varchar(max)), ', ') as payment_types
            ");

        // Apply status filter on aggregated subquery
        $wrap = DB::query()->fromSub($sub, 'x');
        if ($status) {
            $wrap->where(function($w) use ($status) {
                if ($status === 'equal')     $w->whereRaw('paid_amount = total');
                if ($status === 'underpaid')  $w->whereRaw('paid_amount < total');
                if ($status === 'overpaid') $w->whereRaw('paid_amount > total');
            });
        }

        // Final projection for DataTables (nice date string, discrepancy)
        $query = $wrap->selectRaw("
            doc_id,
            CONVERT(varchar(19), [date], 120) as [date],  -- YYYY-MM-DD HH:MM:SS
            branch_name, sales_name, customer_name,
            branch_code, sales_code, customer_code,
            total,
            paid_amount,
            (paid_amount - total) as discrepancy,
            payment_types
        ");

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('method', function($row) {
                // Remove duplicates and extra spaces
                if (empty($row->payment_types)) return '-';
                $types = array_unique(array_map('trim', explode(',', $row->payment_types)));
                return implode(', ', $types);
            })
            ->order(function ($query) {
                $query->orderByDesc('date');
            })
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
                return $row->created_at ?? '';
            })
            ->addColumn('updated_at', function($row) {
                return $row->updated_at ?? '';
            })
            ->addColumn('details', function($model) {
                return view('datatables.details-master-branch', compact('model'))->render();
            })
            ->order(function ($query) {
                $query->orderByDesc('created_at');
            })
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
                return $row->created_at ?? '';
            })
            ->addColumn('updated_at', function($row) {
                return $row->updated_at ?? '';
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
                $query->whereRaw('1 = 0'); // No branch assigned, return empty
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
                return $row->created_at ?? '';
            })
            ->addColumn('updated_at', function($row) {
                return $row->updated_at ?? '';
            })
            ->addColumn('action', function($model) {
                return view('datatables.action-master-store', compact('model'))->render();
            })
            ->order(function ($query) {
                $query->orderByDesc('created_at');
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function masterTransaction(Request $request)
    {
        $user = auth()->user();
        $isBranchAdmin = $user->role === 'branch_admin';
        $isCompanyAdmin = $user->role === 'company_admin';

        $query = Transaction::with(['branch', 'customer', 'detailTransactions']);

        if ($isCompanyAdmin) {
            $rechecked = $request->input('recheck');
            if ($rechecked === 'true') {
                $query->where('is_rechecked', 1);
            } else if ($rechecked === 'all') {
                // Explicitly show all approved, regardless of is_rechecked
                $query->whereIn('is_rechecked', [0, 1])
                    ->where('is_approved', 1); // Always only approved
            } else {
                // Default: show only unchecked
                $query->where('is_rechecked', 0)
                    ->where('is_approved', 1); // Always only approved
            }
        } else if ($isBranchAdmin) {
            $branch = Branch::where('branch_admin', $user->code)->first();
            if ($branch) {
                $query->where('branch_code', $branch->code);
            } else {
                // No branch assigned, return empty
                $query->whereRaw('1=0');
            }
            
            $approval = $request->input('approval');
            if ($approval === 'true') {
                $query->where('is_approved', 1);
            } else if ($approval === 'false') {
                $query->where('is_approved', 0);
            } else if ($approval === 'rechecked') {
                $query->where('is_rechecked', 1);
            }
            // 'all' => no filter
        }

        $dt = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('doc_id', fn($row) => $row->doc_id)
            ->addColumn('date', fn($row) => $row->date)
            ->addColumn('sales_code', fn($row) => $row->sales_code ?? '')
            ->addColumn('customer_code', fn($row) => $row->customer?->code ?? '')
            ->addColumn('total', fn($row) => $row->total ?? 0)
            ->addColumn('paid_amount', fn($row) => $row->paid_amount ?? 0)
            ->addColumn('created_by', fn($row) => $row->created_by ?? '')
            ->addColumn('updated_by', fn($row) => $row->sales_code ?? '')
            ->addColumn('is_approved', fn($row) => (bool) $row->is_approved)
            ->addColumn('details', function ($model) {
                return view('datatables.details-master-transaction', compact('model'))->render();
            })
            // APPROVE column: visible for both roles.
            ->addColumn('approve', function ($model) {
                if (!$model->is_approved) {
                    return view('datatables.approve-master-transaction', compact('model'))->render();
                }
                if ($model->is_rechecked) {
                    return '<span class="badge bg-secondary">RECHECKED</span>';
                }
                return '<span class="badge bg-success">APPROVED</span>';
            })
            // CHECKLIST column: only meaningful for company_admin; return '' for others
            ->addColumn('checklist', function ($model) use ($isCompanyAdmin) {
                if ($isCompanyAdmin && $model->is_approved) {
                    return view('datatables.checklist-master-transaction', compact('model'))->render();
                }
            })
            ->order(function ($q) {
                $q->orderByDesc('date');
            })
            ->rawColumns(['details', 'approve', 'checklist']);

        return $dt->toJson();
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
                return $row->amount;
            })
            ->addColumn('bank', function($row) {
                return $row->bank ? $row->bank : '-';
            })
            ->addColumn('bank_doc', function($row) {
                return $row->bank_doc ? $row->bank_doc : '-';
            })
            ->addColumn('bank_due', function($row) {
                return $row->bank_due ? $row->bank_due : '-';
            })
            ->addColumn('location', function($row) {
                return $row->location ? $row->location : '-';
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
                // Only include logs where the related transaction is in this branch
                $query->whereHas('transaction', function($q) use ($branchCode) {
                    $q->where('branch_code', $branchCode);
                });
            } else {
                // No branch assigned, return empty
                $query->whereRaw('1=0');
            }
        }
        // company_admin: no filter, see all logs

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('transaction_code', function($row) {
                return $row->transaction ? $row->transaction->doc_id : '-';
            })
            ->addColumn('nominal_before', function($row) {
                return $row->transaction ? $row->transaction->total : '-';
            })
            ->addColumn('nominal_after', function($row) {
                return $row->nominal_after ?? '-';
            })
            ->addColumn('status', function($row) {
                return $row->status ?? '-';
            })
            ->addColumn('done_by', function($row) {
                return $row->user ? $row->user->username : '-';
            })
            ->order(function ($query) {
                $query->orderByDesc('created_at');
            })
            ->toJson();
    }
}
?>