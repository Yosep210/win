<?php

namespace App\Livewire\Rank;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\RankForm;
use Livewire\Component;

class Index extends Component
{
    public function create()
    {
        $this->dispatch('open-dynamic-modal', config: RankForm::make(
            title: 'Tambah Rank Baru',
            successMessage: 'Data rank berhasil ditambahkan.',
        ))
            ->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.rank.index');
    }
}
