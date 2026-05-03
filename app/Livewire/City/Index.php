<?php

namespace App\Livewire\City;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\CityForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: CityForm::make(
            title: 'Tambah City Baru',
            successMessage: 'Data city berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.city.index');
    }
}
