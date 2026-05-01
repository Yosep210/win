<?php

namespace App\Support\Forms;

use Spatie\Permission\Models\Permission;

class PermissionForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-permissionTable';
    public const EDIT_EVENT = 'permission:edit';
    public const DELETE_EVENT = 'permission:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Permission berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Permission::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.name.unique' => 'Nama permission sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'Permission Name',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:permissions,name,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan nama permission',
                ],
                [
                    'name' => 'guard_name',
                    'label' => 'Guard',
                    'type' => 'select',
                    'validation' => ['required', 'string', 'max:255'],
                    'options' => ['web' => 'web', 'api' => 'api'],
                    'default' => 'web',
                ],
            ],
        ];
    }
}
