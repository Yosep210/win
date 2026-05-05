<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-end gap-2">
        <flux:button wire:click="create">Add</flux:button>
    </div>
    <livewire:Supplier.Supplier-table />

    <livewire:dynamic-modal-form />
</div>