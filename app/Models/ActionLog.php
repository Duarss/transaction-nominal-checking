<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'nominal_before',
        'nominal_after',
        'status',
        'done_by',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_code', 'doc_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'done_by', 'username');
    }
}
