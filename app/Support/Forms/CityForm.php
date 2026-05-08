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
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama kota',
                ],
                [
                    'name' => 'type',
                    'label' => 'Type',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'Masukkan type kota',
                ],
            ],
        ];
    }
}
