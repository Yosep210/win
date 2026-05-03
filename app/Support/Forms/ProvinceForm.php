<?php

namespace App\Support\Forms;

use App\Models\Province;

class ProvinceForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-provinceTable';

    public const EDIT_EVENT = 'edit-province';

    public const DELETE_EVENT = 'delete-province';

    public static function make(string $title, string $successMessage): array
    {
        return [
            'title' => $title,
            'modelClass' => Province::class,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'Province Name',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                ],
                [
                    'name' => 'code',
                    'label' => 'Province Code',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:10'],
                ],
            ],
        ];
    }
}
