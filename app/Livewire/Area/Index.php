<?php

namespace App\Livewire\Area;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\AreaForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: AreaForm::make(
            title: 'Tambah Area Baru',
            successMessage: 'Data area berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.area.index');
    }
}
