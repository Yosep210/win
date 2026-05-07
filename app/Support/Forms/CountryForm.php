<?php

namespace App\Support\Forms;

use App\Models\Country;

class CountryForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-countryTable';

    public const EDIT_EVENT = 'country:edit';

    public const DELETE_EVENT = 'country:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data country berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Country::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.iso.unique' => 'ISO sudah digunakan.',
                'data.iso3.unique' => 'ISO3 sudah digunakan.',
                'data.numcode.unique' => 'Kode numerik sudah digunakan.',
                'data.phone_code.unique' => 'Kode telepon sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'iso',
                    'label' => 'ISO',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'size:2', 'unique:countries,iso,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan ISO',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Country',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama country',
                ],
                [
                    'name' => 'nice_name',
                    'label' => 'Nama Country (Nice Name)',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama country (nice name)',
                ],
                [
                    'name' => 'iso3',
                    'label' => 'ISO3',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'size:3', 'unique:countries,iso3,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan ISO3',
                ],
                [
                    'name' => 'numcode',
                    'label' => 'Kode Numerik',
                    'type' => 'number',
                    'validation' => ['nullable', 'integer', 'min:0', 'max:65535', 'unique:countries,numcode,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode numerik country',
                ],
                [
                    'name' => 'phone_code',
                    'label' => 'Kode Telepon',
                    'type' => 'number',
                    'validation' => ['required', 'integer', 'min:0', 'unique:countries,phone_code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode telepon',
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
