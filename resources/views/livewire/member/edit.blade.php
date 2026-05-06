<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                {{ __('Edit Member') }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Perbarui informasi profil dan akun anggota.') }}
            </p>
        </div>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-950">
        <form wire:submit="save" class="space-y-8">
            @include('livewire.member.form-fields', ['isEdit' => true])

            <div class="flex items-center justify-between border-t border-neutral-200 pt-6 dark:border-neutral-700">
                <flux:button href="{{ route('member.index') }}" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>

                <div class="flex gap-2">
                    <flux:button href="{{ route('member.show', $user->id) }}" variant="ghost" wire:navigate>
                        {{ __('View Details') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Save Changes') }}</span>
                        <span wire:loading>{{ __('Saving...') }}</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>