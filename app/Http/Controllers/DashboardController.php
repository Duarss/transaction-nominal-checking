<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchName = null;

        if ($user && $user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            $branchName = $branch ? $branch->name : null;
        }

        return view('dashboard.index', [
            'role' => $user->role,
            'branchName' => $branchName,
        ]);

        // return match ($user->role) {
        //     'company_admin' => view('dashboard.company-admin.index'),
        //     'branch_admin' => view('dashboard.branch-admin.index', compact('branchName')),
        //     default => abort(403, 'Unauthorized.'),
        // };
    }
}
