<input
    type="checkbox"
    @checked($checked)
    wire:click="togglePermission({{ $permissionId }})"
    wire:loading.attr="disabled"
    class="h-5 w-5 cursor-pointer rounded border-zinc-300"
    aria-label="Toggle permission"
>
