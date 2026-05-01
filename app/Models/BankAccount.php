<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'bank_id',
    'account_number',
    'account_name',
    'is_primary',
])]
class BankAccount extends Model {}
