<?php

namespace App\Livewire\District;

use App\Livewire\DynamicModalForm;
use App\Models\District;
use App\Support\Forms\DistrictForm;
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
        $allowedSorts = ['city_name', 'name', 'postal_code', 'external_id'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'districts.id';

        // Map alias fields to actual table columns for ROW_NUMBER
        $rowNumberSortField = match ($sortField) {
            'city_name' => 'cities.name',
            'name' => 'districts.name',
            'postal_code' => 'districts.postal_code',
            'external_id' => 'districts.external_id',
            default => 'districts.id'
        };

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return District::query()
            ->leftJoin('cities', 'districts.city_id', '=', 'cities.id')
            ->select('districts.*', 'cities.name as city_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$rowNumberSortField.' '.$sortDirection.') AS no');
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
            ->add('city_name')
            ->add('name')
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

    #[On(DistrictForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: DistrictForm::make(
            title: 'Edit District',
            modelId: $rowId,
            successMessage: 'Data District berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
        // Flux::toast(variant: 'warning', text: "Fitur edit District belum diimplementasikan. ID: {$rowId}");
    }

    #[On(DistrictForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $district = District::findOrFail($rowId);
        $district->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data District berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(District $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(DistrictForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->confirm('Are you sure you want to delete this district?')
                ->dispatch(DistrictForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
