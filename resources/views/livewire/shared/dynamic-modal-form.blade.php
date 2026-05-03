<flux:modal name="{{ $modalName }}" class="min-w-100 space-y-6">
    <div>
        <flux:heading size="lg">{{ $title }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        @foreach ($fields as $field)
        @php
        $name = $field['name'] ?? '';
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? str($name)->headline();
        $placeholder = $field['placeholder'] ?? null;
        $options = $field['options'] ?? [];
        @endphp

        @if ($type === 'textarea')
        <flux:textarea wire:model="data.{{ $name }}" label="{{ $label }}" placeholder="{{ $placeholder }}" />
        @elseif ($type === 'checkbox')
        <div class="flex items-center gap-3">
            <flux:checkbox wire:model="data.{{ $name }}" />
            <flux:label>{{ $label }}</flux:label>
        </div>
        @elseif ($type === 'select')
        <flux:select wire:model="data.{{ $name }}" label="{{ $label }}">
            <flux:select.option value="">Pilih {{ $label }}</flux:select.option>
            @foreach ($options as $key => $val)
            @php
            $optionValue = is_array($val) ? ($val['id'] ?? $val['value'] ?? $key) : $key;
            $optionLabel = is_array($val) ? ($val['name'] ?? $val['label'] ?? 'Unknown') : $val;
            @endphp
            <flux:select.option value="{{ (string) $optionValue }}">
                {{ (string) $optionLabel }}
            </flux:select.option>
            @endforeach
        </flux:select>
        @else
        <flux:input wire:model="data.{{ $name }}"
            type="{{ in_array($type, ['number', 'email', 'date']) ? $type : 'text' }}" label="{{ $label }}"
            placeholder="{{ $placeholder }}" />
        @endif

        @error("data.$name")
        <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @endforeach

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ $cancelLabel }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">{{ $submitLabel }}</flux:button>
        </div>
    </form>
</flux:modal>