<?php

namespace App\Support\Forms;

use App\Models\Regency;
use App\Models\Village;

class VillageForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-villageTable';

    public const EDIT_EVENT = 'village:edit';

    public const DELETE_EVENT = 'village:delete';

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
            'fields' => [
                [
                    'name' => 'regency_id',
                    'label' => 'Regency',
                    'type' => 'select',
                    'options' => Regency::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['required', 'exists:regencies,id'],
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
                    'validation' => ['nullable', 'string', 'max:10'],
                    'placeholder' => 'Masukkan kode pos',
                ],
            ],
        ];
    }
}
