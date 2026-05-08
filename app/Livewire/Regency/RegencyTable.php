<?php

namespace App\Livewire\Regency;

use App\Livewire\DynamicModalForm;
use App\Models\Regency;
use App\Support\Forms\RegencyForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RegencyTable extends PowerGridComponent
{
    public string $tableName = 'regencyTable';

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
        $allowedSorts = ['city_name', 'name', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'regencies.id';

        $rowNumberSortField = match ($sortField) {
            'city_name' => 'cities.name',
            'name' => 'regencies.name',
            'created_at' => 'regencies.created_at',
            default => 'regencies.id',
        };

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Regency::query()
            ->leftJoin('cities', 'regencies.city_id', '=', 'cities.id')
            ->select('regencies.*', 'cities.name as city_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$rowNumberSortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'city' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('city_name')
            ->add('name');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('City', 'city_name')->sortable(),
            Column::make('Regency', 'name')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('city_name')->operators(['contains']),
            Filter::InputText('name')->operators(['contains']),
        ];
    }

    #[On(RegencyForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: RegencyForm::make(
            title: 'Edit Regency',
            modelId: $rowId,
            successMessage: 'Data Regency berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(RegencyForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $regency = Regency::findOrFail($rowId);
        $regency->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data Regency berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Regency $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(RegencyForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this regency?')
                ->dispatch(RegencyForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
