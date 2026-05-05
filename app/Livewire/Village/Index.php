<?php

namespace App\Livewire\Village;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\VillageForm;
use Livewire\Component;

class Index extends Component
{
    public function create()
    {
        $this->dispatch('open-dynamic-modal', config: VillageForm::make(
            title: 'Tambah Village Baru',
            successMessage: 'Data village berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.village.index');
    }
}
