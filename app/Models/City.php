<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['province_id', 'name', 'type', 'code', 'postal_code', 'external_id'])]
class City extends Model
{
    public $timestamps = false;

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
