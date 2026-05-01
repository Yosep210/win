<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'package_id',
    'rank_id',
    'as_stockist',
    'is_stockist_central',
    'stockist_name',
    'stockist_province_id',
    'stockist_city_id',
    'stockist_district_id',
    'stockist_village',
    'stockist_address',
    'wd_status',
    'wd_min',
    'is_ro_enabled',
    'joined_at',
    'upgraded_at',
    'stockist_at',
    'last_ro_at',
])]
class Membership extends Model
{
    protected function casts(): array
    {
        return [
            'is_stockist_central' => 'boolean',
            'is_ro_enabled' => 'boolean',
            'joined_at' => 'datetime',
            'upgraded_at' => 'datetime',
            'stockist_at' => 'datetime',
            'last_ro_at' => 'datetime',
        ];
    }
}
