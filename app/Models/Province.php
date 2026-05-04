<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'code'])]
class Province extends Model
{
    public $timestamps = false;

    public static function provinceCount(): int
    {
        return self::count();
    }
}
