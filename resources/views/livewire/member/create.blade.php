<div class="flex flex-col gap-6">
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="px-6 py-8 md:px-10">
            <div class="mb-6">
                <flux:heading size="lg">{{ __('Create Member') }}</flux:heading>
                <flux:text>{{ __('Samakan data member dengan struktur register lama dan schema baru Laravel.') }}
                </flux:text>
            </div>

            <form wire:submit="save" class="space-y-8">
                <section class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Account Information') }}</flux:heading>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input wire:model="username" :label="__('Username')" type="text" required
                            autocomplete="username" :placeholder="__('Username')" />
                        <flux:input wire:model="name" :label="__('Full Name')" type="text" required autofocus
                            autocomplete="name" :placeholder="__('Full name')" />
                        <flux:input wire:model="email" :label="__('Email Address')" type="email" required
                            autocomplete="email" placeholder="email@example.com" />
                        <flux:input wire:model="phone" :label="__('Phone / WhatsApp')" type="text" required
                            autocomplete="tel" placeholder="+628123456789" />
                        <flux:input wire:model="password" :label="__('Password')" type="password" required
                            autocomplete="new-password" :placeholder="__('Password')" viewable />
                        <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password"
                            required autocomplete="new-password" :placeholder="__('Confirm password')" viewable />
                    </div>
                </section>

                <section class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Identity Information') }}</flux:heading>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input wire:model="birthDate" :label="__('Birth Date')" type="date" required />

                        <flux:select wire:model="gender" :label="__('Gender')">
                            <flux:select.option value="">{{ __('Select gender') }}</flux:select.option>
                            <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                            <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                        </flux:select>

                        <flux:input wire:model="idNumber" :label="__('ID Card Number')" type="text" required
                            placeholder="320xxxxxxxxxxxxx" />
                        <flux:input wire:model="npwp" :label="__('NPWP')" type="text" placeholder="Optional" />
                    </div>
                </section>

                <section class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Address Information') }}</flux:heading>
                    </div>

                    @if ($provinces->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ __('Master wilayah belum tersedia. Anda masih bisa membuat member, tetapi data alamat detail
                        belum dapat dipilih.') }}
                    </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:select wire:model.live="provinceId" :label="__('Province')">
                            <flux:select.option value="">{{ __('Select province') }}</flux:select.option>
                            @foreach ($provinces as $province)
                            <flux:select.option value="{{ $province->id }}">{{ $province->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="cityId" :label="__('City / Regency')">
                            <flux:select.option value="">{{ __('Select city') }}</flux:select.option>
                            @foreach ($cities as $city)
                            <flux:select.option value="{{ $city->id }}">{{ $city->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="districtId" :label="__('District')">
                            <flux:select.option value="">{{ __('Select district') }}</flux:select.option>
                            @foreach ($districts as $district)
                            <flux:select.option value="{{ $district->id }}">{{ $district->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="villageId" :label="__('Village')">
                            <flux:select.option value="">{{ __('Select village') }}</flux:select.option>
                            @foreach ($villages as $village)
                            <flux:select.option value="{{ $village->id }}">{{ $village->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ __('Address')
                            }}</label>
                        <textarea wire:model="address" rows="3"
                            class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                            placeholder="{{ __('Street address, RT/RW, notes, etc.') }}"></textarea>
                        @error('address')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                <section class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Bank Information') }}</flux:heading>
                    </div>

                    @if ($banks->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ __('Master bank belum tersedia. Bagian rekening bisa dilengkapi nanti.') }}
                    </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:select wire:model="bankId" :label="__('Bank')">
                            <flux:select.option value="">{{ __('Select bank') }}</flux:select.option>
                            @foreach ($banks as $bank)
                            <flux:select.option value="{{ $bank->id }}">{{ $bank->code }} - {{ $bank->name }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="accountNumber" :label="__('Account Number')" type="text"
                            placeholder="1234567890" />
                        <flux:input wire:model="accountName" :label="__('Account Holder Name')" type="text"
                            placeholder="{{ __('Full name on bank account') }}" />
                    </div>
                </section>

                <section class="space-y-4">
                    <div>
                        <flux:heading size="sm">{{ __('Referral & Membership') }}</flux:heading>
                    </div>

                    @if ($packages->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ __('Package register belum tersedia. Member akan dibuat tanpa package terlebih dahulu.') }}
                    </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input wire:model.live="sponsorUsername" :label="__('Sponsor Username')" type="text"
                            placeholder="{{ __('Optional sponsor username') }}" />
                        <flux:input :label="__('Sponsor Name')" type="text" :value="$sponsorName" readonly />

                        <flux:select wire:model="packageId" :label="__('Package')">
                            <flux:select.option value="">{{ __('Select package') }}</flux:select.option>
                            @foreach ($packages as $package)
                            <flux:select.option value="{{ $package->id }}">{{ $package->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="asStockist" :label="__('Member Type')">
                            <flux:select.option value="member">{{ __('Member') }}</flux:select.option>
                            <flux:select.option value="stockist">{{ __('Stockist') }}</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:checkbox wire:model="isStockistCentral" :label="__('Set as central stockist')" />
                </section>

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