<?php

namespace App\Livewire\Member;

use App\Models\User;
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

final class UserTable extends PowerGridComponent
{
    private const DELETE_EVENT = 'member:user-delete';

    public string $tableName = 'userTable';

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
        $allowedSorts = ['name', 'username', 'email', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return User::query()
            ->with('roles')
            ->withoutRole(['admin', 'staff'])
            ->select('users.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY users.'.$sortField.' '.$sortDirection.') AS no');
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
            ->add('username')
            ->add('email')
            ->add('roles', fn (User $user) => $user->roles->pluck('name')->join(', '))
            ->add('created_at_formatted', fn (User $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Name', 'name')->sortable(),
            Column::make('Username', 'username')->sortable(),
            Column::make('Email', 'email')->sortable(),
            Column::make('Roles', 'roles'),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('username')->operators(['contains']),
            Filter::inputText('email')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(self::DELETE_EVENT)]
    public function delete(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('admin')) {
            Flux::toast(variant: 'warning', text: 'You cannot delete an admin user.');

            return;
        }

        $user->delete();

        Flux::toast(variant: 'success', text: 'User deleted successfully.');
        $this->dispatch('$commit')->self();
    }

    public function actions(User $row): array
    {
        if (! auth()->user()?->hasRole('admin')) {
            return [];
        }

        return [
            Button::add('show')
                ->slot('Show')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->route('member.show', ['user' => $row->id]),
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->route('member.edit', ['user' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->confirm('Are you sure you want to delete this user?')
                ->dispatch(self::DELETE_EVENT, ['userId' => $row->id]),
        ];
    }
}
