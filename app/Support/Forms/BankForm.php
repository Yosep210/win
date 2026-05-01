<?php

namespace App\Support\Forms;

use App\Models\Bank;

class BankForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-bankTable';
    public const EDIT_EVENT = 'bank:edit';
    public const DELETE_EVENT = 'bank:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data bank berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Bank::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'code',
                    'label' => 'Kode Bank',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:banks,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Contoh: BCA',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Bank',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama bank',
                ],
                [
                    'name' => 'status',
                    'label' => 'Aktif',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => true,
                ],
            ],
        ];
    }
}
