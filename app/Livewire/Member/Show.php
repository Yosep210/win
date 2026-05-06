<?php

namespace App\Livewire\Member;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load([
            $this->profile(),
            $this->membership(),
            $this->network(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.member.show');
    }
}
