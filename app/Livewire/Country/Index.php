<?php

namespace App\Livewire\Country;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\CountryForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: CountryForm::make(
            title: 'Tambah Country Baru',
            successMessage: 'Data country berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.country.index');
    }
}
