<?php

namespace App\Livewire\Province;

use App\Models\Province;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ProvinceTable extends PowerGridComponent
{
    private const EDIT_EVENT = 'province:edit';

    public string $tableName = 'provinceTable';

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
        return Province::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('code');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Province', 'name')->sortable(),
            Column::make('Code', 'code')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('code')->operators(['contains']),
        ];
    }

    #[On(self::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        Flux::toast(variant: 'warning', text: "Fitur edit Province belum diimplementasikan. ID: {$rowId}");
    }

    public function actions(Province $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: ' . $row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(self::EDIT_EVENT, ['rowId' => $row->id]),
        ];
    }
}
