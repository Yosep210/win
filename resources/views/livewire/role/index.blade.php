<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-end">
        <flux:button wire:click="create">Add Role</flux:button>
    </div>

    <livewire:role.role-table />

    <livewire:dynamic-modal-form />
</div>
