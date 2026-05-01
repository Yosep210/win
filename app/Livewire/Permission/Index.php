<?php

namespace App\Livewire\Permission;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\PermissionForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: PermissionForm::make(
            title: 'Tambah Permission Baru',
            successMessage: 'Permission berhasil ditambahkan.',
        ))->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.permission.index');
    }
}
