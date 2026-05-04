<?php

namespace App\Livewire\Suppliers;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\SuppliersForm;
use Livewire\Component;

class Index extends Component
{
    public function create()
    {
        $this->dispatch('open-dynamic-modal', config: SuppliersForm::make(
            title: 'Tambah Supplier Baru',
            successMessage: 'Data supplier berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.suppliers.index');
    }
}
