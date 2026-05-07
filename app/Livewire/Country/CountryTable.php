<?php

namespace App\Livewire\Country;

use App\Livewire\DynamicModalForm;
use App\Models\Country;
use App\Support\Forms\CountryForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CountryTable extends PowerGridComponent
{
    public string $tableName = 'countryTable';

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
        $allowedSorts = ['id', 'iso', 'name', 'nice_name', 'iso3', 'numcode', 'phone_code', 'status'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Country::query()
            ->select('countries.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY countries.'.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('iso')
            ->add('name')
            ->add('nice_name')
            ->add('iso3')
            ->add('numcode')
            ->add('phone_code')
            ->add('status');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Iso', 'iso')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Nice name', 'nice_name')->sortable(),
            Column::make('Iso3', 'iso3')->sortable(),
            Column::make('Num code', 'numcode')->sortable(),
            Column::make('Phone code', 'phone_code')->sortable(),
            Column::make('Status', 'status')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('iso')->operators(['contains']),
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('nice_name')->operators(['contains']),
            Filter::InputText('iso3')->operators(['contains']),
            Filter::InputText('numcode')->operators(['contains']),
            Filter::InputText('phone_code')->operators(['contains']),
        ];
    }

    #[On(CountryForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: CountryForm::make(
            title: 'Edit Country',
            modelId: $rowId,
            successMessage: 'Data Country berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(CountryForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $country = Country::findOrFail($rowId);
        $country->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data Country berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Country $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(CountryForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this country?')
                ->dispatch(CountryForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
