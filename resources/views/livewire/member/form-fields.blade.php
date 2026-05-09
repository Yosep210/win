<section class="space-y-4">
    <div>
        <flux:heading size="sm">{{ __('Account Information') }}</flux:heading>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <flux:input wire:model="username" :label="__('Username')" type="text" required autocomplete="username" />
        <flux:input wire:model="name" :label="__('Full Name')" type="text" required autocomplete="name" />
        <flux:input wire:model="email" :label="__('Email Address')" type="email" required autocomplete="email" />

        <div class="flex flex-col gap-2">
            <flux:label>{{ __('Phone / WhatsApp') }}</flux:label>
            <div class="flex items-start">
                {{-- Selector Kode Negara --}}
                <flux:select wire:model.live="phoneCountryId" class="rounded-r-none! w-36 shrink-0 border-r-0">
                    @foreach ($countries as $country)
                    <flux:select.option value="{{ $country->id }}">{{ strtoupper($country->iso ?? '??') }} (+{{
                        $country->phone_code }})</flux:select.option>
                    @endforeach
                </flux:select>
                {{-- Input Nomor Telepon --}}
                <flux:input wire:model="phone" type="text" required autocomplete="tel" class="flex-1 !rounded-l-none"
                    prefix="+{{ $this->getSelectedCountryPhoneCode() }}" placeholder="812345678" />
            </div>
            <flux:error name="phone" />
        </div>

        {{-- Tampilkan password hanya saat membuat member baru --}}
        @if (!($isEdit ?? false))
        <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password"
            viewable />
        <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required
            autocomplete="new-password" viewable />
        @endif
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

        <flux:input wire:model="idNumber" :label="__('ID Card Number')" type="text" required />
        <flux:input wire:model="npwp" :label="__('NPWP')" type="text" :placeholder="__('Optional')" />
    </div>
</section>

<section class="space-y-4">
    <div>
        <flux:heading size="sm">{{ __('Address Information') }}</flux:heading>
    </div>

    @if ($provinces->isEmpty())
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        {{ __('Master wilayah belum tersedia.') }}
    </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <flux:select wire:model.live="countryId" :label="__('Country')">
            <flux:select.option value="">{{ __('Select country') }}</flux:select.option>
            @foreach ($countries as $country)
            <flux:select.option value="{{ $country->id }}">{{ $country->name }}</flux:select.option>
            @endforeach
        </flux:select>

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

        <flux:select wire:model.live="regencyId" :label="__('Regency')">
            <flux:select.option value="">{{ __('Select regency') }}</flux:select.option>
            @foreach ($regencies as $regency)
            <flux:select.option value="{{ $regency->id }}">{{ $regency->name }}</flux:select.option>
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
        <label class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ __('Address') }}</label>
        <textarea wire:model="address" rows="3"
            class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            placeholder="{{ __('Street address, RT/RW, etc.') }}"></textarea>
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
        {{ __('Master bank belum tersedia.') }}
    </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <flux:select wire:model="bankId" :label="__('Bank')">
            <flux:select.option value="">{{ __('Select bank') }}</flux:select.option>
            @foreach ($banks as $bank)
            <flux:select.option value="{{ $bank->id }}">{{ $bank->code }} - {{ $bank->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:input wire:model="accountNumber" :label="__('Account Number')" type="text" />
        <flux:input wire:model="accountName" :label="__('Account Holder Name')" type="text" />
    </div>
</section>

<section class="space-y-4">
    <div>
        <flux:heading size="sm">{{ __('Referral & Membership') }}</flux:heading>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @if (!($isEdit ?? false))
        <flux:input wire:model.live="sponsorUsername" :label="__('Sponsor Username')" type="text"
            placeholder="{{ __('Optional') }}" />
        <flux:input :label="__('Sponsor Name')" type="text" :value="$sponsorName" readonly />
        @endif

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

    <div class="flex flex-col gap-2">
        <flux:checkbox wire:model="isStockistCentral" :label="__('Set as central stockist')" />
        @if ($isEdit ?? false)
        <flux:checkbox wire:model="isActive" :label="__('Member is active')" />
        @endif
    </div>
</section>