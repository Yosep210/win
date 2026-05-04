<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'address', 'contact_id', 'status'])]
class Suppliers extends Model
{
    protected function casts(): array
    {
        return [
            'contact_id' => 'integer',
            'status' => 'boolean',
        ];
    }
}
