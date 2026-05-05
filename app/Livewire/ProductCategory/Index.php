<?php

namespace App\Livewire\ProductCategory;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\ProductCategoryForm;
use Livewire\Component;

class Index extends Component
{
    public function create()
    {
        $this->dispatch('open-dynamic-modal', config: ProductCategoryForm::make(
            title: 'Tambah Kategori Produk Baru',
            successMessage: 'Data kategori produk berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.product-categories.index');
    }
}
