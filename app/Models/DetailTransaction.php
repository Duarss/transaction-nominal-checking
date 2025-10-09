<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaction extends Model
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

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'doc_id', 'doc_id');
    }
}
