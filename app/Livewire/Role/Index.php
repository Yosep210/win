<?php

namespace App\Livewire\Role;

use App\Livewire\DynamicModalForm;
use App\Support\Forms\RoleForm;
use Livewire\Component;

class Index extends Component
{
    public function create(): void
    {
        $this->dispatch('open-dynamic-modal', config: RoleForm::make(
            title: 'Tambah Role Baru',
            successMessage: 'Role berhasil ditambahkan.',
        ))->to(DynamicModalForm::class);
    }

    public function render()
    {
        return view('livewire.role.index');
    }
}
