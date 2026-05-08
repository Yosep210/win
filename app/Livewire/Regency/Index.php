<?php

namespace App\Livewire\Regency;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\RegencyForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: RegencyForm::make(
            title: 'Tambah Regency Baru',
            successMessage: 'Data regency berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.regency.index');
    }
}
