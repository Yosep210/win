<?php

namespace App\Support\Forms;

use App\Models\Rank;

class RankForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-rankTable';

    public const EDIT_EVENT = 'rank:edit';

    public const DELETE_EVENT = 'rank:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data rank berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Rank::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode sudah digunakan.',
                'data.name.unique' => 'Nama rank sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'Nama Rank',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:ranks,name,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan nama rank',
                ],
                [
                    'name' => 'code',
                    'label' => 'Kode Rank',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:ranks,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode rank',
                ],
                [
                    'name' => 'sort_order',
                    'label' => 'Urutan',
                    'type' => 'number',
                    'validation' => ['required', 'integer', 'min:0'],
                    'default' => 0,
                ],
                [
                    'name' => 'is_active',
                    'label' => 'Aktif',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => true,
                ],
            ],
        ];
    }
}
