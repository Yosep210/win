<?php

namespace App\Support\Forms;

use App\Models\Country;
use App\Models\Province;

class ProvinceForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-provinceTable';

    public const EDIT_EVENT = 'province:edit';

    public const DELETE_EVENT = 'province:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data province berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Province::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'fields' => [
                [
                    'name' => 'country_id',
                    'label' => 'Country',
                    'type' => 'select',
                    'options' => Country::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['required', 'exists:countries,id'],
                ],
                [
                    'name' => 'name',
                    'label' => 'Province Name',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama province',
                ],
            ],
        ];
    }
}
