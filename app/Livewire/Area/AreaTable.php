<?php

namespace App\Livewire\Area;

use App\Livewire\DynamicModalForm;
use App\Models\Area;
use App\Support\Forms\AreaForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class AreaTable extends PowerGridComponent
{
    public string $tableName = 'areaTable';

    public string $sortField = 'name';

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
        $allowedSorts = ['name', 'code'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Area::query()
            ->select('areas.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY areas.'.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('id')
            ->add('name')
            ->add('code');
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Name', 'name')->sortable(),
            Column::make('Code', 'code')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('code')->operators(['contains']),
        ];
    }

    #[On(AreaForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: AreaForm::make(
            title: 'Edit Area',
            modelId: $rowId,
            successMessage: 'Data area berhasil diperbarui.',
        ))
            ->to(DynamicModalForm::class);
    }

    #[On(AreaForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $area = Area::findOrFail($rowId);
        $area->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data area berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Area $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(AreaForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this area?')
                ->dispatch(AreaForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
