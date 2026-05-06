<div class="flex flex-col gap-6">
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="px-6 py-8 md:px-10">
            <div class="mb-6">
                <flux:heading size="lg">{{ __('Create Member') }}</flux:heading>
                <flux:text>{{ __('Samakan data member dengan struktur register lama dan schema baru Laravel.') }}
                </flux:text>
            </div>

            <form wire:submit="save" class="space-y-8">
                @include('livewire.member.form-fields', ['isEdit' => false])

                <div class="flex items-center justify-between">
                    <flux:button :href="route('member.index')" type="button" wire:navigate>{{ __('Back') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Create member') }}</span>
                        <span wire:loading wire:target="save">{{ __('Creating...') }}</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>