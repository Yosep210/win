<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['district_id', 'name', 'postal_code', 'external_id'])]
class Village extends Model
{
    public $timestamps = false;

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
