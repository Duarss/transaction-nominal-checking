<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainPaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_id',
        'item_index',
        'payment_type',
        'amount',
        'bank',
        'bank_doc',
        'bank_due',
        'location',
    ];

    public function mainPayment()
    {
        return $this->belongsTo(MainPayment::class, 'doc_id', 'doc_id');
    }
}
