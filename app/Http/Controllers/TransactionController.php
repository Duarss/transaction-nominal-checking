<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\DetailTransaction;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Traits\HasResponse;
use App\Traits\HasTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use HasResponse, HasTransaction;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Transaction::class);
        $title = "Master Transaction";

        return view('master.transactions.index', compact('title'));
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
    public function show($id)
    {
        //
    }

    public function viewDetails(Transaction $transaction)
    {
        $this->authorize('viewDetails', $transaction);

        return view('master.transactions.details', [
            'title' => 'Detail Transaksi ' . $transaction->doc_id,
            'data'  => $transaction,
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
    public function update(TransactionRequest $request, Transaction $transaction, TransactionService $service)
    {
        $this->authorize('update', $transaction);

        return $this->handle(__FUNCTION__, function () use ($request, $transaction, $service) {
            return $service->update($transaction, $request->validated());
        });
    }

    public function approve(TransactionRequest $request, Transaction $transaction, TransactionService $service)
    {
        $this->authorize('approve', $transaction);

        return $this->handle(__FUNCTION__, function () use ($request, $transaction, $service) {
            return $service->approve($transaction, $request->validated());
        });
    }

    public function recheck(TransactionRequest $request, Transaction $transaction, TransactionService $service)
    {
        $this->authorize('recheck', $transaction);

        return $this->handle(__FUNCTION__, function () use ($request, $transaction, $service) {
            return $service->recheck($transaction, $request->validated());
        });
    }

    // public function unrecheck(TransactionRequest $request, Transaction $transaction, TransactionService $service)
    // {
    //     $this->authorize('unrecheck', $transaction);

    //     return $this->handle(__FUNCTION__, function () use ($request, $transaction, $service) {
    //         return $service->unrecheck($transaction, $request->validated());
    //     });
    // }

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

    public function getDetailsJson($doc_id)
    {
        $details = DetailTransaction::where('doc_id', $doc_id)->get();
        return response()->json(['details' => $details]);
    }
}
