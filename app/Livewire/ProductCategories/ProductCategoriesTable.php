<?php

namespace App\Livewire\ProductCategories;

use App\Livewire\DynamicModalForm;
use App\Models\ProductCategories;
use App\Support\Forms\ProductCategoriesForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ProductCategoriesTable extends PowerGridComponent
{
    public string $tableName = 'productCategoriesTable';

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
        $allowedSorts = ['id', 'code', 'name', 'status', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return ProductCategories::query()
            ->select('product_categories.*')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY product_categories.'.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('code')
            ->add('name')
            ->add('status')
            ->add('created_at_formatted', fn (ProductCategories $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Code', 'code')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Status', 'status')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('code')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('status')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(ProductCategoriesForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: ProductCategoriesForm::make(
            title: 'Edit Product Category',
            modelId: $rowId,
            successMessage: 'Data kategori produk berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(ProductCategoriesForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $productCategory = ProductCategories::findOrFail($rowId);
        $productCategory->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data product category berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(ProductCategories $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(ProductCategoriesForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this product category?')
                ->dispatch(ProductCategoriesForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
