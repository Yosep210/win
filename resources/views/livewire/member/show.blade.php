<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">
                {{ $user->name }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $user->username }} &middot; {{ $user->email }}
            </p>
        </div>

        <div class="flex gap-2">
            <flux:button href="{{ route('member.index') }}" wire:navigate>
                {{ __('Back') }}
            </flux:button>
            <flux:button href="{{ route('member.edit', ['user' => $user->id]) }}" variant="primary" wire:navigate>
                {{ __('Edit') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div
            class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-950">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Account Details') }}</h2>
            <dl class="grid gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Username') }}</dt>
                    <dd class="col-span-2">{{ $user->username }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Email') }}</dt>
                    <dd class="col-span-2">{{ $user->email }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Phone') }}</dt>
                    <dd class="col-span-2">{{ $user->phone }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Status') }}</dt>
                    <dd class="col-span-2">{{ ucfirst($user->status) }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Referral Code') }}</dt>
                    <dd class="col-span-2">{{ $user->referral_code }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Verified At') }}</dt>
                    <dd class="col-span-2">{{ optional($user->email_verified_at)->format('d M Y H:i') ?? __('Not
                        verified') }}</dd>
                </div>
            </dl>
        </div>

        <div
            class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-950">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Profile & Membership') }}</h2>
            <dl class="grid gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Gender') }}</dt>
                    <dd class="col-span-2">{{ optional($user->profile)->gender ?? __('-') }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Birth Date') }}</dt>
                    <dd class="col-span-2">{{ optional($user->profile?->birth_date)->format('d M Y') ?? __('-') }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('ID Number') }}</dt>
                    <dd class="col-span-2">{{ optional($user->profile)->id_number ?? __('-') }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('NPWP') }}</dt>
                    <dd class="col-span-2">{{ optional($user->profile)->npwp ?? __('-') }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Package') }}</dt>
                    <dd class="col-span-2">{{ $user->membership?->package?->name ?? __('-') }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Joined At') }}</dt>
                    <dd class="col-span-2">{{ optional($user->membership?->joined_at)->format('d M Y H:i') ?? __('-') }}
                    </dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Bank Account') }}</dt>
                    <dd class="col-span-2">{{ $user->bankAccounts->first()?->bank_id ?
                        ($user->bankAccounts->first()?->account_name .' • '.
                        $user->bankAccounts->first()?->account_number) : __('-') }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <dt class="font-medium">{{ __('Sponsor') }}</dt>
                    <dd class="col-span-2">{{ $user->network?->sponsor_id ? __('Assigned') : __('None') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>