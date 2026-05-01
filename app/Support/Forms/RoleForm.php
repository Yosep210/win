<?php

namespace App\Support\Forms;

use Spatie\Permission\Models\Role;

class RoleForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-roleTable';
    public const EDIT_EVENT = 'role:edit';
    public const DELETE_EVENT = 'role:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Role berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Role::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.name.unique' => 'Nama role sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'Role Name',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:roles,name,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan nama role',
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
