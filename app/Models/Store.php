<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'branch_code',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'code');
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
