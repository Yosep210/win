<?php

namespace App\Livewire\Village;

use App\Livewire\DynamicModalForm;
use App\Models\Village;
use App\Support\Forms\VillageForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class VillageTable extends PowerGridComponent
{
    public string $tableName = 'villageTable';

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
        $allowedSorts = ['district_name', 'name', 'postal_code', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'villages.id';

        $rowNumberSortField = match ($sortField) {
            'district_name' => 'districts.name',
            'name' => 'villages.name',
            'postal_code' => 'villages.postal_code',
            'created_at' => 'villages.created_at',
            default => 'villages.id',
        };

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Village::query()
            ->leftJoin('districts', 'villages.district_id', '=', 'districts.id')
            ->select('villages.*', 'districts.name as district_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$rowNumberSortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'district' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('district_name')
            ->add('name')
            ->add('postal_code');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('District', 'district_name')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Postal code', 'postal_code')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('district_name')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('postal_code')->operators(['contains']),
        ];
    }

    #[On(VillageForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: VillageForm::make(
            title: 'Edit Village',
            modelId: $rowId,
            successMessage: 'Data village berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(VillageForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $village = Village::findOrFail($rowId);
        $village->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data village berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Village $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(VillageForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this village?')
                ->dispatch(VillageForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
