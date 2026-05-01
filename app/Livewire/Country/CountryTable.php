<?php

namespace App\Livewire\Country;

use App\Models\Country;
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
    private const EDIT_EVENT = 'country:edit';

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
        return Country::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('iso')
            ->add('name')
            ->add('nice_name')
            ->add('iso3')
            ->add('num_code')
            ->add('phone_code')
            ->add('status');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Iso', 'iso')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Nice name', 'nice_name')->sortable(),
            Column::make('Iso3', 'iso3')->sortable(),
            Column::make('Num code', 'num_code')->sortable(),
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
            Filter::InputText('num_code')->operators(['contains']),
            Filter::InputText('phone_code')->operators(['contains']),
        ];
    }

    #[On(self::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        Flux::toast(variant: 'warning', text: "Fitur edit Country belum diimplementasikan. ID: {$rowId}");
    }

    public function actions(Country $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(self::EDIT_EVENT, ['rowId' => $row->id]),
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
