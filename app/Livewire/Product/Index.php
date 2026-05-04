<?php

namespace App\Livewire\Product;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\ProductForm;
use Livewire\Component;

class Index extends Component
{
    public function create()
    {
        $this->dispatch('open-dynamic-modal', config: ProductForm::make(
            title: 'Tambah Product Baru',
            successMessage: 'Data product berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.product.index');
    }
}
