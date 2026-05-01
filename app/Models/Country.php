<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['iso', 'name', 'nice_name', 'iso3', 'num_code', 'phone_code', 'status'])]
class Country extends Model
{
    public $timestamps = false;
}
