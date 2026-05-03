<?php

namespace App\Livewire\Province;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\ProvinceForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: ProvinceForm::make(
            title: 'Tambah Province Baru',
            successMessage: 'Data province berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.province.index');
    }
}
