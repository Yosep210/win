<?php

namespace App\Livewire\Role;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Show extends Component
{
    public Role $role;

    public function mount(Role $role): void
    {
        $this->role = $role->loadCount('permissions');
    }

    public function render(): View
    {
        return view('livewire.role.show');
    }
}
