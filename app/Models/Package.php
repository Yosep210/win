<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'name',
    'sort_order',
    'package_count',
    'bv',
    'price',
    'sponsor_percent',
    'passup_percent',
    'pairing_percent',
    'pairing_nominal',
    'pairing_max',
    'pairing_point',
    'reward_point',
    'description',
    'is_register',
    'is_order',
    'is_active',
])]
class Package extends Model {}
