<?php

namespace App\Livewire\Permission;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\PermissionForm;
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
use Spatie\Permission\Models\Permission;

final class PermissionTable extends PowerGridComponent
{
    public string $tableName = 'permissionTable';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

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
        $allowedSorts = ['name', 'guard_name', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'name';
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
            ->add('guard_name')
            ->add('created_at_formatted', fn (Permission $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Name', 'name')->sortable(),
            Column::make('Guard', 'guard_name')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('guard_name')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(PermissionForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $permission = Permission::findOrFail($rowId);

        if ($permission->name === 'admin') {
            Flux::toast(variant: 'danger', text: 'You cannot edit the admin permission.');

            return;
        }

        $this->dispatch('open-dynamic-modal', config: PermissionForm::make(
            title: 'Edit Permission',
            modelId: $rowId,
            successMessage: 'Permission berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(PermissionForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $permission = Permission::findOrFail($rowId);

        if ($permission->name === 'admin') {
            Flux::toast(variant: 'danger', text: 'You cannot delete the admin permission.');

            return;
        }

        $permission->delete();

        Flux::toast(variant: 'success', text: 'Permission berhasil dihapus.');
        $this->dispatch('$commit')->self();
    }

    public function actions(Permission $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(PermissionForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->confirm('Are you sure you want to delete this permission?')
                ->dispatch(PermissionForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
