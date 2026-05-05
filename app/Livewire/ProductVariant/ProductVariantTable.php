<?php

namespace App\Livewire\ProductVariant;

use App\Livewire\DynamicModalForm;
use App\Models\ProductVariant;
use App\Support\Forms\ProductVariantForm;
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

final class ProductVariantTable extends PowerGridComponent
{
    public string $tableName = 'productVariantTable';

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
        $allowedSorts = ['product_name', 'code', 'name', 'price', 'bv', 'status', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'product_variants.id';

        // Map alias fields to actual table columns for ROW_NUMBER
        $rowNumberSortField = match ($sortField) {
            'product_name' => 'products.name',
            'name' => 'product_variants.name',
            'sku' => 'product_variants.sku',
            'price' => 'product_variants.price',
            'stock' => 'product_variants.stock',
            'status' => 'product_variants.status',
            default => 'product_variants.id'
        };

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return ProductVariant::query()
            ->leftJoin('products', 'product_variants.product_id', '=', 'products.id')
            ->select('product_variants.*', 'products.name as product_name')
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY '.$rowNumberSortField.' '.$sortDirection.') AS no');
    }

    public function relationSearch(): array
    {
        return [
            'product' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('no')
            ->add('product_name')
            ->add('code')
            ->add('name')
            ->add('price')
            ->add('bv')
            ->add('status')
            ->add('created_at_formatted', fn (ProductVariant $model) => Carbon::parse($model->created_at)->format('d M Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'no'),
            Column::make('Product', 'product_name', 'product_id')->sortable(),
            Column::make('Code', 'code')->sortable(),
            Column::make('Name', 'name')->sortable(),
            Column::make('Price', 'price')->sortable(),
            Column::make('BV', 'bv')->sortable(),
            Column::make('Status', 'status')->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('product_name')->operators(['contains']),
            Filter::inputText('code')->operators(['contains']),
            Filter::inputText('name')->operators(['contains']),
            Filter::inputText('price')->operators(['contains']),
            Filter::inputText('bv')->operators(['contains']),
            Filter::inputText('status')->operators(['contains']),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[On(ProductVariantForm::EDIT_EVENT)]
    public function edit(int $rowId): void
    {
        $this->dispatch('open-dynamic-modal', config: ProductVariantForm::make(
            title: 'Edit Product Variant',
            modelId: $rowId,
            successMessage: 'Data product variant berhasil diperbarui.',
        ))->to(DynamicModalForm::class);
    }

    #[On(ProductVariantForm::DELETE_EVENT)]
    public function delete(int $rowId): void
    {
        $productVariant = ProductVariant::findOrFail($rowId);
        $productVariant->delete();

        Flux::toast(
            variant: 'success',
            text: 'Data product variant berhasil dihapus.',
        );

        $this->dispatch('$commit')->self();
    }

    public function actions(ProductVariant $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit')
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch(ProductVariantForm::EDIT_EVENT, ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('Delete')
                ->class('pg-btn-white dark:ring-pg-red-600 dark:border-pg-red-600 dark:hover:bg-pg-red-700 dark:ring-offset-pg-red-800 dark:text-pg-red-300 dark:bg-pg-red-700')
                ->confirm('Are you sure you want to delete this product variant?')
                ->dispatch(ProductVariantForm::DELETE_EVENT, ['rowId' => $row->id]),
        ];
    }
}
