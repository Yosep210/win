<?php

namespace App\Livewire\District;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\DistrictForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: DistrictForm::make(
            title: 'Tambah District Baru',
            successMessage: 'Data district berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.district.index');
    }
}
