<?php

namespace App\Livewire\Member;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Province;
use App\Models\User;
use App\Models\UserNetwork;
use App\Models\UserProfile;
use App\Models\Village;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $birthDate = '';

    public string $gender = '';

    public string $idNumber = '';

    public string $npwp = '';

    public ?int $countryId = null;

    public ?int $provinceId = null;

    public ?int $cityId = null;

    public ?int $districtId = null;

    public ?int $villageId = null;

    public string $address = '';

    public ?int $bankId = null;

    public string $accountNumber = '';

    public string $accountName = '';

    public ?int $packageId = null;

    public string $asStockist = 'member';

    public bool $isStockistCentral = false;

    public string $sponsorUsername = '';

    public string $sponsorName = '';

    public function mount(): void
    {
        $this->countryId = Country::query()
            ->where('iso', 'id')
            ->value('id') ?? Country::query()->value('id');
    }

    public function updatedProvinceId(): void
    {
        $this->cityId = null;
        $this->districtId = null;
        $this->villageId = null;
    }

    public function updatedCityId(): void
    {
        $this->districtId = null;
        $this->villageId = null;
    }

    public function updatedDistrictId(): void
    {
        $this->villageId = null;
    }

    public function updatedSponsorUsername(): void
    {
        $this->sponsorName = User::query()
            ->where('username', $this->sponsorUsername)
            ->value('name') ?? '';
    }

    protected function provinces(): Collection
    {
        return Province::query()
            ->orderBy('name')
            ->get();
    }

    protected function cities(): Collection
    {
        if (! $this->provinceId) {
            return new Collection;
        }

        return City::query()
            ->where('province_id', $this->provinceId)
            ->orderBy('name')
            ->get();
    }

    protected function districts(): Collection
    {
        if (! $this->cityId) {
            return new Collection;
        }

        return District::query()
            ->where('city_id', $this->cityId)
            ->orderBy('name')
            ->get();
    }

    protected function villages(): Collection
    {
        if (! $this->districtId) {
            return new Collection;
        }

        return Village::query()
            ->where('district_id', $this->districtId)
            ->orderBy('name')
            ->get();
    }

    protected function banks(): Collection
    {
        return Bank::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    protected function packages(): Collection
    {
        return Package::query()
            ->where('is_active', true)
            ->where('is_register', true)
            ->orderBy('name')
            ->get();
    }

    protected function formRules(): array
    {
        $hasProvinceOptions = Province::query()->exists();
        $hasBankOptions = Bank::query()->where('status', true)->exists();
        $hasPackageOptions = Package::query()
            ->where('is_active', true)
            ->where('is_register', true)
            ->exists();

        return [
            ...$this->profileRules(),
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')],
            'password' => $this->passwordRules(),
            'birthDate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'idNumber' => ['required', 'string', 'max:255', Rule::unique('user_profiles', 'id_number')],
            'npwp' => ['nullable', 'string', 'max:255', Rule::unique('user_profiles', 'npwp')],
            'provinceId' => [Rule::requiredIf($hasProvinceOptions), 'nullable', 'exists:provinces,id'],
            'cityId' => [Rule::requiredIf($hasProvinceOptions), 'nullable', 'exists:cities,id'],
            'districtId' => [Rule::requiredIf($hasProvinceOptions), 'nullable', 'exists:districts,id'],
            'villageId' => ['nullable', 'exists:villages,id'],
            'address' => [Rule::requiredIf($hasProvinceOptions), 'nullable', 'string'],
            'bankId' => [Rule::requiredIf($hasBankOptions), 'nullable', 'exists:banks,id'],
            'accountNumber' => [Rule::requiredIf($hasBankOptions), 'nullable', 'string', 'max:255'],
            'accountName' => [Rule::requiredIf($hasBankOptions), 'nullable', 'string', 'max:255'],
            'packageId' => [Rule::requiredIf($hasPackageOptions), 'nullable', 'exists:packages,id'],
            'asStockist' => ['required', Rule::in(['member', 'stockist'])],
            'sponsorUsername' => ['nullable', 'string', 'exists:users,username', 'different:username'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'birthDate' => 'birth date',
            'idNumber' => 'ID card number',
            'provinceId' => 'province',
            'cityId' => 'city',
            'districtId' => 'district',
            'villageId' => 'village',
            'bankId' => 'bank',
            'accountNumber' => 'account number',
            'accountName' => 'account name',
            'packageId' => 'package',
            'sponsorUsername' => 'sponsor username',
        ];
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', trim($phone)) ?? '';

        if ($phone === '') {
            return '';
        }

        if (str_starts_with($phone, '0')) {
            return '+62'.substr($phone, 1);
        }

        if (! str_starts_with($phone, '+')) {
            return '+'.$phone;
        }

        return $phone;
    }

    protected function resolveSponsor(): ?User
    {
        if ($this->sponsorUsername === '') {
            return null;
        }

        return User::query()
            ->where('username', $this->sponsorUsername)
            ->first();
    }

    protected function resolveUserHuId(?User $sponsor, User $user): int
    {
        if (! $sponsor) {
            return $user->id;
        }

        $sponsorNetwork = UserNetwork::query()
            ->where('user_id', $sponsor->id)
            ->first();

        return $sponsorNetwork?->user_hu_id ?? $sponsor->id;
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (User::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function save(): void
    {
        $validated = $this->validate($this->formRules(), [], $this->validationAttributes());
        $phone = $this->normalizePhone($validated['phone']);
        $sponsor = $this->resolveSponsor();

        if ($phone === '') {
            $this->addError('phone', 'Phone number is required.');

            return;
        }

        if (User::query()->where('phone', $phone)->exists()) {
            $this->addError('phone', 'The phone has already been taken.');

            return;
        }

        DB::transaction(function () use ($validated, $phone, $sponsor): void {
            $user = User::create([
                'name' => $validated['name'],
                'username' => Str::lower($validated['username']),
                'email' => Str::lower($validated['email']),
                'phone' => $phone,
                'password' => $validated['password'],
                'status' => 'active',
                'agree_ethic' => true,
                'email_verified_at' => now(),
                'referral_code' => $this->generateReferralCode(),
            ]);

            $user->assignRole('member');

            UserProfile::create([
                'user_id' => $user->id,
                'gender' => $validated['gender'],
                'birth_date' => $validated['birthDate'],
                'id_number' => $validated['idNumber'],
                'npwp' => $validated['npwp'] ?: null,
                'address' => $validated['address'] ?: null,
                'country_id' => $this->countryId,
                'province_id' => $validated['provinceId'] ?: null,
                'city_id' => $validated['cityId'] ?: null,
                'district_id' => $validated['districtId'] ?: null,
                'village_id' => $validated['villageId'] ?: null,
            ]);

            Membership::create([
                'user_id' => $user->id,
                'package_id' => $validated['packageId'] ?: null,
                'as_stockist' => $validated['asStockist'],
                'is_stockist_central' => $this->isStockistCentral,
                'stockist_name' => $validated['asStockist'] === 'stockist' ? $validated['name'] : null,
                'joined_at' => now(),
                'stockist_at' => $validated['asStockist'] === 'stockist' ? now() : null,
            ]);

            if ($validated['bankId'] && $validated['accountNumber'] && $validated['accountName']) {
                BankAccount::create([
                    'user_id' => $user->id,
                    'bank_id' => $validated['bankId'],
                    'account_number' => $validated['accountNumber'],
                    'account_name' => Str::upper($validated['accountName']),
                    'is_primary' => true,
                ]);
            }

            UserNetwork::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor?->id,
                'parent_id' => null,
                'position' => null,
                'generation' => 0,
                'level' => 0,
                'group' => 0,
                'user_hu_id' => $this->resolveUserHuId($sponsor, $user),
            ]);
        });

        $this->reset([
            'name',
            'username',
            'email',
            'phone',
            'password',
            'password_confirmation',
            'birthDate',
            'gender',
            'idNumber',
            'npwp',
            'provinceId',
            'cityId',
            'districtId',
            'villageId',
            'address',
            'bankId',
            'accountNumber',
            'accountName',
            'packageId',
            'sponsorUsername',
            'sponsorName',
        ]);

        $this->countryId = Country::query()
            ->where('iso', 'id')
            ->value('id') ?? Country::query()->value('id');
        $this->asStockist = 'member';
        $this->isStockistCentral = false;
        $this->resetValidation();

        Flux::toast(variant: 'success', text: 'Member created successfully.');
    }

    public function render(): View
    {
        return view('livewire.member.create', [
            'provinces' => $this->provinces(),
            'cities' => $this->cities(),
            'districts' => $this->districts(),
            'villages' => $this->villages(),
            'banks' => $this->banks(),
            'packages' => $this->packages(),
        ]);
    }
}
