<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['district_id', 'name', 'postal_code', 'external_id'])]
class Village extends Model
{
    public $timestamps = false;
}
