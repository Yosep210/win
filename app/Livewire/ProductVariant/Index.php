<?php

namespace App\Livewire\ProductVariant;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\ProductVariantForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: ProductVariantForm::make(
            title: 'Tambah Product Variant Baru',
            successMessage: 'Data product variant berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.product-variant.index');
    }
}
