<?php

namespace App\Livewire\Province;

use App\Livewire\DynamicModalForm;
use App\Models\Province;
use App\Support\Forms\ProvinceForm;
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
        $allowedSorts = ['country_name', 'name', 'code'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'provinces.id';

        // Map alias fields to actual table columns for ROW_NUMBER
        $rowNumberSortField = match ($sortField) {
            'country_name' => 'countries.name',
            'name' => 'provinces.name',
            'code' => 'provinces.code',
            default => 'provinces.id'
        };

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Province::query()
            ->leftJoin('countries', 'provinces.countrie_id', '=', 'countries.id')
            ->select('provinces.*', 'countries.name as country_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY ' . $rowNumberSortField . ' ' . $sortDirection . ') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'country' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('country_name')
            ->add('name')
            ->add('code');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Country', 'country_name')->sortable(),
            Column::make('Province', 'name')->sortable(),
            Column::make('Code', 'code')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::InputText('country_name')->operators(['contains']),
            Filter::InputText('name')->operators(['contains']),
            Filter::InputText('code')->operators(['contains']),
        ];
    }

    #[On(ProvinceForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: ProvinceForm::make(
            title: 'Edit Province',
            modelId: $rowId,
            successMessage: 'Data province berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(ProvinceForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $province = Province::findOrFail($rowId);
        $province->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data Province berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Province $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(ProvinceForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this province?')
                ->dispatch(ProvinceForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
