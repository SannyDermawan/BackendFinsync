<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'wallet',
        'account_name',
        'password',
        'saldo'
    ];

    protected $hidden = [
        'password'
    ];
}