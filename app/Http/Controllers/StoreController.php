<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Branch;
use App\Models\Store;
use App\Services\StoreService;
use App\Traits\HasResponse;
use App\Traits\HasTransaction;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    use HasResponse, HasTransaction;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $this->authorize('viewAny', Store::class);
        $title = "Master Store";

        if ($user && $user->role === 'branch_admin') {
            $branch = Branch::where('branch_admin', $user->code)->first();
            $branchName = $branch ? $branch->name : null;
        }

        return view('master.stores.index', [
            'title' => $title,
            'role' => $user->role,
            'branchName' => $branchName ?? null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Store $store)
    {
        $this->authorize('view', $store);
        
        $store->branch;

        return response()->json([
            'success' => true,
            'data' => $store->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRequest $request, Store $store, StoreService $service)
    {
        $this->authorize('update', Store::class);

        return $this->handle(__FUNCTION__, function() use ($request, $store, $service) {
            return $service->update($store, $request->validated());
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
