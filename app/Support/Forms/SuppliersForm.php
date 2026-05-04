<?php

namespace App\Support\Forms;

use App\Models\Suppliers;

class SuppliersForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-suppliersTable';

    public const EDIT_EVENT = 'suppliers:edit';

    public const DELETE_EVENT = 'suppliers:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data supplier berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Suppliers::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.name.unique' => 'Nama supplier sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'Nama Supplier',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:100', 'unique:suppliers,name,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan nama supplier',
                ],
                [
                    'name' => 'email',
                    'label' => 'Email Supplier',
                    'type' => 'email',
                    'validation' => ['nullable', 'email', 'max:50'],
                    'placeholder' => 'Masukkan email supplier',
                ],
                [
                    'name' => 'phone',
                    'label' => 'Telepon Supplier',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:20'],
                    'placeholder' => 'Masukkan nomor telepon supplier',
                ],
                [
                    'name' => 'address',
                    'label' => 'Alamat Supplier',
                    'type' => 'textarea',
                    'validation' => ['nullable', 'string'],
                    'placeholder' => 'Masukkan alamat supplier',
                ],
                [
                    'name' => 'contact_id',
                    'label' => 'Contact ID',
                    'type' => 'number',
                    'validation' => ['nullable', 'integer', 'min:0'],
                    'placeholder' => 'Masukkan contact id',
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
