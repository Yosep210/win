<?php

namespace App\Support\Forms;

use App\Models\Package;

class PackageForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-packageTable';

    public const EDIT_EVENT = 'package:edit';

    public const DELETE_EVENT = 'package:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data package berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Package::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'code',
                    'label' => 'Kode Package',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:packages,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Contoh: PKG001',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Package',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama package',
                ],
                [
                    'name' => 'sort_order',
                    'label' => 'Urutan Sortir',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => '0',
                    'default' => 0,
                ],
                [
                    'name' => 'package_count',
                    'label' => 'Jumlah Item',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:1'],
                    'placeholder' => '1',
                    'default' => 1,
                ],
                [
                    'name' => 'bv',
                    'label' => 'Business Value (BV)',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan nilai BV',
                    'default' => 0,
                ],
                [
                    'name' => 'price',
                    'label' => 'Harga',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan harga',
                    'default' => 0,
                ],
                [
                    'name' => 'sponsor_percent',
                    'label' => 'Persentase Sponsor (%)',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0', 'max:100'],
                    'placeholder' => '0',
                    'default' => 0,
                ],
                [
                    'name' => 'passup_percent',
                    'label' => 'Persentase Passup (%)',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0', 'max:100'],
                    'placeholder' => '0',
                    'default' => 0,
                ],
                [
                    'name' => 'pairing_percent',
                    'label' => 'Persentase Pairing (%)',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0', 'max:100'],
                    'placeholder' => '0',
                    'default' => 0,
                ],
                [
                    'name' => 'pairing_nominal',
                    'label' => 'Nominal Pairing',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan nominal pairing',
                    'default' => 0,
                ],
                [
                    'name' => 'pairing_max',
                    'label' => 'Maksimal Pairing',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan maksimal pairing',
                    'default' => 0,
                ],
                [
                    'name' => 'pairing_point',
                    'label' => 'Poin Pairing',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan poin pairing',
                    'default' => 0,
                ],
                [
                    'name' => 'reward_point',
                    'label' => 'Poin Reward',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan poin reward',
                    'default' => 0,
                ],
                [
                    'name' => 'description',
                    'label' => 'Deskripsi',
                    'type' => 'textarea',
                    'validation' => ['nullable', 'string', 'max:1000'],
                    'placeholder' => 'Masukkan deskripsi package',
                ],
                [
                    'name' => 'is_register',
                    'label' => 'Untuk Registrasi',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => false,
                ],
                [
                    'name' => 'is_order',
                    'label' => 'Untuk Order',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => false,
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
