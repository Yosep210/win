<?php

namespace App\Support\Forms;

use App\Models\City;
use App\Models\Province;

class CityForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-cityTable';

    public const EDIT_EVENT = 'city:edit';

    public const DELETE_EVENT = 'city:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data city berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => City::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'province_id',
                    'label' => 'Province',
                    'type' => 'select',
                    'options' => Province::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['required', 'exists:provinces,id'],
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama City',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:255', 'unique:cities,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan code kota',
                ],
                [
                    'name' => 'type',
                    'label' => 'Type',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'Masukkan type kota',
                ],
                [
                    'name' => 'code',
                    'label' => 'Code',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:50'],
                ],
                [
                    'name' => 'postal_code',
                    'label' => 'Postal Code',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:10'],
                ],
            ],
        ];
    }
}
