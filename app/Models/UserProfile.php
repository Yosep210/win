<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'gender',
    'birth_date',
    'id_number',
    'npwp',
    'address',
    'country_id',
    'province_id',
    'city_id',
    'district_id',
    'village_id',
    'photo',
    'id_card_photo',
])]
class UserProfile extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }
}
