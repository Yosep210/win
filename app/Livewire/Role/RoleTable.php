<?php

namespace App\Livewire\Role;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\RoleForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Role;

final class RoleTable extends PowerGridComponent
{
    public string $tableName = 'roleTable';

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
        $allowedSorts = ['name', 'guard_name', 'permissions_count', 'created_at'];

        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';
        $orderBy = match ($sortField) {
            'permissions_count' => '(select count(*) from role_has_permissions where roles.id = role_has_permissions.role_id)',
            default => 'roles.'.$sortField,
        };

        return Role::query()
            ->select('roles.*')
            ->withCount('permissions')
            ->selectRaw("ROW_NUMBER() OVER (ORDER BY {$orderBy} {$sortDirection}) as no");
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
            ->add('guard_name')
            ->add('permissions_count')
            ->add('created_at_formatted', fn (Role $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Name', 'name')->sortable(),
            Column::make('Guard', 'guard_name')->sortable(),
            Column::make('Permissions', 'permissions_count')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('guard_name')->operators(['contains']),
            Filter::inputText('permissions_count')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(RoleForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $role = Role::findOrFail($rowId);

        if ($role->name === 'admin') {
            Flux::toast(variant: 'danger', text: 'You cannot edit the admin role.');

            return;
        }

        $this->dispatch('open-dynamic-modal', config: RoleForm::make(
            title: 'Edit Role',
            modelId: $rowId,
            successMessage: 'Role berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(RoleForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $role = Role::findOrFail($rowId);

        if ($role->name === 'admin') {
            Flux::toast(variant: 'danger', text: 'You cannot delete the admin role.');

            return;
        }

        $role->delete();

        Flux::toast(variant: 'success', text: 'Role berhasil dihapus.');
        $this->dispatch('$commit')->self();
    }

    public function actions(Role $row): array
    {
        return [
            Button::add('show')
                ->slot('Show')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->route('role.show', ['role' => $row->id]),
            Button::add('edit')
                ->slot('Edit')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(RoleForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->confirm('Are you sure you want to delete this role?')
                ->dispatch(RoleForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
