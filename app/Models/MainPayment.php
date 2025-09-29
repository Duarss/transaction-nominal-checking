<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_id',
        'tanggal',
        'doc_call_id',
        'branch_id',
        'sales_id',
        'customer_id',
        'total',
        'created_on',
        'last_updated',
        'created_by',
        'updated_by',
    ];

    public function mainPaymentDetails()
    {
        return $this->hasMany(MainPaymentDetail::class, 'doc_id', 'doc_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'code');
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id', 'code');
    }

    public function customer()
    {
        return $this->belongsTo(Store::class, 'customer_id', 'code');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'code');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'code');
    }

    public function getRouteKeyName(): string
    {
        return 'doc_id';
    }
}
