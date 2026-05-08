<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['regency_id', 'name', 'postal_code'])]
class Village extends Model
{
    public $timestamps = false;

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }
}
