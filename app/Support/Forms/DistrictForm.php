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
            'validationMessages' => [
                'data.postal_code.unique' => 'Kode pos sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'city_id',
                    'label' => 'Kota',
                    'type' => 'select',
                    'options' => City::pluck('name', 'id')->toArray(),
                    'validation' => ['required', 'exists:cities,id'],
                    'placeholder' => 'Pilih kota',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama District',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama district',
                ],
                [
                    'name' => 'postal_code',
                    'label' => 'Kode Pos',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:20', 'unique:districts,postal_code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode pos',
                ],
                [
                    'name' => 'external_id',
                    'label' => 'External ID',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'Masukkan external ID (opsional)',
                ],
            ],
        ];
    }
}
