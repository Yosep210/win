<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                {{ $role->name }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Guard: {{ $role->guard_name }} &middot;
                {{ $role->permissions_count ?? $role->permissions->count() }} permissions assigned
            </p>
        </div>
        <flux:button href="{{ route('role.index') }}" wire:navigate>Back</flux:button>
    </div>

    <livewire:role.role-permission-table :roleId="$role->id" />
</div>
