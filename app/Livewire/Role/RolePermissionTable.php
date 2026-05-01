<?php

namespace App\Livewire\Role;

use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionTable extends PowerGridComponent
{
    public string $tableName = 'rolePermissionTable';

    public int $roleId;

    protected ?Role $role = null;

    protected ?array $assignedPermissionIds = null;

    protected function getRole(): Role
    {
        if (! $this->role) {
            $this->role = Role::query()
                ->with('permissions:id')
                ->findOrFail($this->roleId);
        }

        return $this->role;
    }

    protected function assignedPermissionIds(): array
    {
        if ($this->assignedPermissionIds === null) {
            $this->assignedPermissionIds = $this->getRole()
                ->permissions
                ->pluck('id')
                ->all();
        }

        return $this->assignedPermissionIds;
    }

    public function setUp(): array
    {
        return [
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $allowedSorts = ['name', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Permission::query()
            ->select('permissions.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY permissions.'.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('name')
            ->add('created_at_formatted', fn (Permission $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Name', 'name')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    public function togglePermission(int $permissionId): void
    {
        $role = $this->getRole();
        $permission = Permission::findOrFail($permissionId);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            Flux::toast(variant: 'success', text: "Permission '{$permission->name}' was removed from role '{$role->name}'.");
        } else {
            $role->givePermissionTo($permission);
            Flux::toast(variant: 'success', text: "Permission '{$permission->name}' was added to role '{$role->name}'.");
        }

        $this->role = null;
        $this->assignedPermissionIds = null;
    }

    protected function permissionToggleView(Permission $permission): View
    {
        return view('components.role-permission-toggle', [
            'checked' => in_array($permission->id, $this->assignedPermissionIds(), true),
            'permissionId' => $permission->id,
        ]);
    }

    public function actions(Permission $row): array
    {
        return [
            Button::add('toggle-permission')
                ->slot($this->permissionToggleView($row)->render())
                ->class('flex items-center justify-center'),
        ];
    }
}
