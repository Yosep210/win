<?php

namespace App\Livewire\Product;

use App\Livewire\DynamicModalForm;
use App\Models\Product;
use App\Support\Forms\ProductForm;
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

final class ProductTable extends PowerGridComponent
{
    public string $tableName = 'productTable';

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
        $allowedSorts = [
            'category_name' => 'product_categories.name',
            'name' => 'products.name',
            'sku' => 'products.sku',
            'price' => 'products.price',
            'stock' => 'products.stock',
            'status' => 'products.status',
            'created_at' => 'products.created_at',
        ];
        $sortField = $allowedSorts[$this->sortField] ?? 'products.name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Product::query()
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select('products.*', 'product_categories.name as category_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$sortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'category' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('category_name')
            ->add('code')
            ->add('name')
            ->add('description')
            ->add('status')
            ->add('created_at_formatted', fn (Product $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Category', 'category_name', 'category_id')->sortable(),
            Column::make('Code', 'code')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Description', 'description')->sortable(),
            Column::make('Status', 'status')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('category_name')->operators(['contains']),
            Filter::inputText('code')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('description')->operators(['contains']),
            Filter::inputText('status')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(ProductForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: ProductForm::make(
            title: 'Edit Product',
            modelId: $rowId,
            successMessage: 'Data product berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(ProductForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $product = Product::findOrFail($rowId);
        $product->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data product berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(Product $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(ProductForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this product?')
                ->dispatch(ProductForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
