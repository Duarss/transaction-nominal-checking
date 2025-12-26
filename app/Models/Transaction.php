<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_id',
        'date',
        'doc_call_id',
        'branch_code',
        'sales_code',
        'customer_code',
        'total',
        'paid_amount',
        'created_on',
        'last_updated',
        'created_by',
        'updated_by',
    ];

    public function detailTransactions()
    {
        return $this->hasMany(DetailTransaction::class, 'doc_id', 'doc_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'code');
    }

    public function customer()
    {
        return $this->belongsTo(Store::class, 'customer_code', 'code');
    }

    public function getRouteKeyName(): string
    {
        return 'doc_id';
    }
}
