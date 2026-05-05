<?php

namespace App\Livewire\Supplier;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\SupplierForm;
use Livewire\Component;

class Index extends Component
{
    public function create()
    {
        $this->dispatch('open-dynamic-modal', config: SupplierForm::make(
            title: 'Tambah Supplier Baru',
            successMessage: 'Data supplier berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.Supplier.index');
    }
}
