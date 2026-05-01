<?php

namespace App\Livewire\Bank;

use App\Livewire\DynamicModalForm;
use App\Models\Bank;
use App\Support\Forms\BankForm;
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

final class BankTable extends PowerGridComponent
{
    public string $tableName = 'bankTable';

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
        $allowedSorts = ['code', 'name', 'status', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Bank::query()
            ->select('banks.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY banks.'.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('code')
            ->add('name')
            ->add('status')
            ->add('created_at_formatted', fn (Bank $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Code', 'code')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Status', 'status')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('code')->operators(['contains']),
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('status')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(BankForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: BankForm::make(
            title: 'Edit Data Bank',
            modelId: $rowId,
            successMessage: 'Data bank berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(BankForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $bank = Bank::findOrFail($rowId);
        $bank->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data bank berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Bank $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(BankForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this bank?')
                ->dispatch(BankForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
