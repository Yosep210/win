<?php

namespace App\Livewire\City;

use App\Livewire\DynamicModalForm;
use App\Models\City;
use App\Support\Forms\CityForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CityTable extends PowerGridComponent
{
    public string $tableName = 'cityTable';

    public string $sortField = 'province_name';

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
        $allowedSorts = [
            'province_name' => 'provinces.name',
            'name' => 'cities.name',
            'type' => 'cities.type',
        ];

        $sortField = $allowedSorts[$this->sortField] ?? 'provinces.name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return City::query()
            ->leftJoin('provinces', 'cities.province_id', '=', 'provinces.id')
            ->select('cities.*', 'provinces.name as province_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'province' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('province_name')
            ->add('name')
            ->add('type');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Province', 'province_name')->sortable(),
            Column::make('City', 'name')->sortable(),
            Column::make('Type', 'type')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('province_name')->operators(['contains']),
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('type')->operators(['contains']),
        ];
    }

    #[On(CityForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: CityForm::make(
            title: 'Edit Data City',
            modelId: $rowId,
            successMessage: 'Data City berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(CityForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $city = City::findOrFail($rowId);
        $city->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data City berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(City $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(CityForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this city?')
                ->dispatch(CityForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
