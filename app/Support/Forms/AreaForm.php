<?php

namespace App\Support\Forms;

use App\Models\Area;

class AreaForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-areaTable';

    public const EDIT_EVENT = 'area:edit';

    public const DELETE_EVENT = 'area:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data area berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Area::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.name.unique' => 'Nama area sudah digunakan.',
                'data.code.unique' => 'Kode area sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'Nama Area',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:30', 'unique:areas,name,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan nama area',
                ],
                [
                    'name' => 'code',
                    'label' => 'Kode Area',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:10', 'unique:areas,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode area',
                ],
            ],
        ];
    }
}
