<?php

namespace App\Support\Forms;

use App\Models\District;
use App\Models\Village;

class VillageForm
{
    public const REFRESH_EVENT = 'refresh-village-table';

    public const EDIT_EVENT = 'edit-village';

    public const DELETE_EVENT = 'delete-village';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data village berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Village::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.district_id.exists' => 'District ID tidak ditemukan.',
                'data.postal_code.unique' => 'Kode pos sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'district_id',
                    'label' => 'District ID',
                    'type' => 'select',
                    'options' => District::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['required', 'integer'],
                    'placeholder' => 'Masukkan district ID',
                ],
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama village',
                ],
                [
                    'name' => 'postal_code',
                    'label' => 'Postal Code',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:20'],
                    'placeholder' => 'Masukkan kode pos',
                ],
                [
                    'name' => 'external_id',
                    'label' => 'External ID',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:50'],
                    'placeholder' => 'Masukkan external ID',
                ],
            ],
        ];
    }
}
