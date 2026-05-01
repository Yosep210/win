<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-end">
        <flux:button wire:click="create">Add Bank</flux:button>
    </div>

    <livewire:bank.bank-table />

    <livewire:dynamic-modal-form />
</div>
