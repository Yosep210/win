<?php

namespace App\Support\Forms;

use App\Models\City;
use App\Models\District;

class DistrictForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-districtTable';

    public const EDIT_EVENT = 'district:edit';

    public const DELETE_EVENT = 'district:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data district berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => District::class,
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
                    'label' => 'Nama District',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama district',
                ],
            ],
        ];
    }
}
