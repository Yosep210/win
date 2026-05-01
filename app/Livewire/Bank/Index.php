<?php

namespace App\Livewire\Bank;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\BankForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: BankForm::make(
            title: 'Tambah Bank Baru',
            successMessage: 'Data bank berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.bank.index');
    }
}
