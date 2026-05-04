<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['iso', 'name', 'nice_name', 'iso3', 'num_code', 'phone_code', 'status'])]
class Country extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'num_code' => 'integer',
            'phone_code' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'countrie_id');
    }

    public static function countryCount(): int
    {
        return self::count();
    }
}
