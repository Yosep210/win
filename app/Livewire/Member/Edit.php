<?php

namespace App\Livewire\Member;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load(['profile', 'membership.package', 'bankAccounts', 'network']);
    }

    public function render(): View
    {
        return view('livewire.member.edit');
    }
}
