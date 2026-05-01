<?php

namespace App\Livewire\District;

use App\Models\District;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class DistrictTable extends PowerGridComponent
{
    private const EDIT_EVENT = 'district:edit';

    public string $tableName = 'districtTable';

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
        $allowedSorts = ['city_id', 'name', 'postal_code', 'external_id'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return District::query()
            ->with('city')
            ->select('districts.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY districts.' . $sortField . ' ' . $sortDirection . ') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'city' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('city_name', fn(District $district) => $district->city?->name)
            ->add('postal_code')
            ->add('external_id');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('City', 'city_name')->sortable(),
            Column::make('District', 'name')->sortable(),
            Column::make('Postal code', 'postal_code')->sortable(),
            Column::make('External id', 'external_id')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('city_name')->operators(['contains']),
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('postal_code')->operators(['contains']),
            Filter::InputText('external_id')->operators(['contains']),
        ];
    }

    #[On(self::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        Flux::toast(variant: 'warning', text: "Fitur edit District belum diimplementasikan. ID: {$rowId}");
    }

    public function actions(District $row): array
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
