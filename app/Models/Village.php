<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['district_id', 'name', 'postal_code'])]
class Village extends Model
{
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
