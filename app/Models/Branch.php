<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'branch_admin',
    ];

    public function stores()
    {
        return $this->hasMany(Store::class, 'branch_code', 'code');
    }

    public function branchAdmin()
    {
        return $this->hasOne(User::class, 'code', 'branch_admin');
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
