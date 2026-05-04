<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['country_id', 'name', 'code'])]
class Province extends Model
{
    public $timestamps = false;

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public static function provinceCount(): int
    {
        return self::count();
    }

    public function getCountryNameAttribute()
    {
        return $this->country?->name;
    }
}
