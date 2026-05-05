<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'status'])]
class ProductCategory extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
