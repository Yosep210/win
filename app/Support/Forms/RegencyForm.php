<?php

namespace App\Support\Forms;

use App\Models\City;
use App\Models\Regency;

class RegencyForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-regencyTable';

    public const EDIT_EVENT = 'regency:edit';

    public const DELETE_EVENT = 'regency:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data regency berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Regency::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'fields' => [
                [
                    'name' => 'city_id',
                    'label' => 'Kota',
                    'type' => 'select',
                    'options' => City::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['required', 'exists:cities,id'],
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Regency',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama regency',
                ],
            ],
        ];
    }
}
