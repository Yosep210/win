<?php

namespace App\Livewire\Supplier;

use App\Livewire\DynamicModalForm;
use App\Models\Supplier;
use App\Support\Forms\SupplierForm;
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

final class SupplierTable extends PowerGridComponent
{
    public string $tableName = 'SupplierTable';

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
        $allowedSorts = ['id', 'name', 'email', 'phone', 'contact_id', 'status', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Supplier::query()
            ->select('Supplier.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY Supplier.'.$sortField.' '.$sortDirection.') AS no');
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
            ->add('email')
            ->add('phone')
            ->add('contact_id')
            ->add('status')
            ->add('address')
            ->add('created_at_formatted', fn (Supplier $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Name', 'name')->sortable(),
            Column::make('Email', 'email')->sortable(),
            Column::make('Phone', 'phone')->sortable(),
            Column::make('Contact ID', 'contact_id')->sortable(),
            Column::make('Status', 'status')->sortable(),
            Column::make('Address', 'address')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('email')->operators(['contains']),
            Filter::InputText('phone')->operators(['contains']),
            Filter::InputText('contact_id')->operators(['contains']),
            Filter::InputText('status')->operators(['contains']),
            Filter::InputText('address')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(SupplierForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: SupplierForm::make(
            title: 'Edit Supplier',
            modelId: $rowId,
            successMessage: 'Data supplier berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(SupplierForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $supplier = Supplier::findOrFail($rowId);
        $supplier->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data supplier berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Supplier $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(SupplierForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this supplier?')
                ->dispatch(SupplierForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
