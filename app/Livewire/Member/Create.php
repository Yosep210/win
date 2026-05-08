<?php

namespace App\Livewire\Member;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Livewire\Member\Concerns\InteractsWithMemberFormData;
use App\Models;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    use InteractsWithMemberFormData;
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

    public ?int $regencyId = null;

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
        $this->countryId = $this->defaultCountryId();
    }

    public function updatedProvinceId(): void
    {
        $this->cityId = null;
        $this->regencyId = null;
        $this->villageId = null;
    }

    public function updatedCityId(): void
    {
        $this->regencyId = null;
        $this->villageId = null;
    }

    public function updatedRegencyId(): void
    {
        $this->villageId = null;
    }

    public function updatedSponsorUsername(): void
    {
        $this->sponsorUsername = Str::lower(trim($this->sponsorUsername));
        $this->sponsorName = $this->lookupSponsorName($this->sponsorUsername);
    }

    protected function validationAttributes(): array
    {
        return [
            'birthDate' => 'birth date',
            'countryId' => 'country',
            'idNumber' => 'ID card number',
            'provinceId' => 'province',
            'cityId' => 'city',
            'regencyId' => 'regency',
            'villageId' => 'village',
            'bankId' => 'bank',
            'accountNumber' => 'account number',
            'accountName' => 'account name',
            'packageId' => 'package',
            'sponsorUsername' => 'sponsor username',
        ];
    }

    protected function formRules(): array
    {
        $hasProvinceOptions = $this->hasProvinceOptions();
        $hasBankOptions = $this->hasBankOptions();
        $hasPackageOptions = $this->hasPackageOptions();

        return [
            ...$this->profileRules(),
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')],
            'password' => $this->passwordRules(),
            'birthDate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'idNumber' => ['required', 'string', 'max:255', Rule::unique('user_profiles', 'id_number')],
            'npwp' => ['nullable', 'string', 'max:255', Rule::unique('user_profiles', 'npwp')],
            'countryId' => ['nullable', $this->countryExistsRule()],
            'provinceId' => [Rule::requiredIf($hasProvinceOptions), 'nullable', $this->provinceExistsRule()],
            'cityId' => [Rule::requiredIf($hasProvinceOptions), 'nullable', $this->cityExistsRule()],
            'regencyId' => [Rule::requiredIf($hasProvinceOptions), 'nullable', $this->regencyExistsRule()],
            'villageId' => ['nullable', $this->villageExistsRule()],
            'address' => [Rule::requiredIf($hasProvinceOptions), 'nullable', 'string'],
            'bankId' => [Rule::requiredIf($hasBankOptions), 'nullable', $this->bankExistsRule()],
            'accountNumber' => [Rule::requiredIf($hasBankOptions), 'nullable', 'string', 'max:255'],
            'accountName' => [Rule::requiredIf($hasBankOptions), 'nullable', 'string', 'max:255'],
            'packageId' => [Rule::requiredIf($hasPackageOptions), 'nullable', $this->packageExistsRule()],
            'asStockist' => ['required', Rule::in(['member', 'stockist'])],
            'sponsorUsername' => ['nullable', 'string', 'exists:users,username', 'different:username'],
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

    protected function sanitizeString(?string $value): string
    {
        return trim((string) $value);
    }

    protected function normalizeFormState(): void
    {
        $this->name = $this->sanitizeString($this->name);
        $this->username = Str::lower($this->sanitizeString($this->username));
        $this->email = Str::lower($this->sanitizeString($this->email));
        $this->phone = $this->normalizePhone($this->phone);
        $this->idNumber = $this->sanitizeString($this->idNumber);
        $this->npwp = $this->sanitizeString($this->npwp);
        $this->address = $this->sanitizeString($this->address);
        $this->accountNumber = $this->sanitizeString($this->accountNumber);
        $this->accountName = $this->sanitizeString($this->accountName);
        $this->sponsorUsername = Str::lower($this->sanitizeString($this->sponsorUsername));
        $this->sponsorName = $this->lookupSponsorName($this->sponsorUsername);
    }

    protected function lookupSponsorName(string $username): string
    {
        if ($username === '') {
            return '';
        }

        return User::query()
            ->where('username', $username)
            ->value('name') ?? '';
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

        $sponsorNetwork = Models\UserNetwork::query()
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
        $this->normalizeFormState();

        $validated = $this->validate($this->formRules(), [], $this->validationAttributes());
        $sponsor = $this->resolveSponsor();

        DB::transaction(function () use ($validated, $sponsor): void {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'status' => 'active',
                'agree_ethic' => true,
                'email_verified_at' => now(),
                'referral_code' => $this->generateReferralCode(),
            ]);

            $user->assignRole('member');

            Models\UserProfile::create([
                'user_id' => $user->id,
                'gender' => $validated['gender'],
                'birth_date' => $validated['birthDate'],
                'id_number' => $validated['idNumber'],
                'npwp' => $validated['npwp'] ?: null,
                'address' => $validated['address'] ?: null,
                'country_id' => $this->countryId,
                'province_id' => $validated['provinceId'] ?: null,
                'city_id' => $validated['cityId'] ?: null,
                'regency_id' => $validated['regencyId'] ?: null,
                'village_id' => $validated['villageId'] ?: null,
            ]);

            Models\Membership::create([
                'user_id' => $user->id,
                'package_id' => $validated['packageId'] ?: null,
                'as_stockist' => $validated['asStockist'],
                'is_stockist_central' => $this->isStockistCentral,
                'stockist_name' => $validated['asStockist'] === 'stockist' ? $validated['name'] : null,
                'joined_at' => now(),
                'stockist_at' => $validated['asStockist'] === 'stockist' ? now() : null,
            ]);

            if ($validated['bankId'] && $validated['accountNumber'] && $validated['accountName']) {
                Models\BankAccount::create([
                    'user_id' => $user->id,
                    'bank_id' => $validated['bankId'],
                    'account_number' => $validated['accountNumber'],
                    'account_name' => Str::upper($validated['accountName']),
                    'is_primary' => true,
                ]);
            }

            Models\UserNetwork::create([
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
            'regencyId',
            'villageId',
            'address',
            'bankId',
            'accountNumber',
            'accountName',
            'packageId',
            'sponsorUsername',
            'sponsorName',
        ]);

        $this->countryId = $this->defaultCountryId();
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
            'regencies' => $this->regencies(),
            'villages' => $this->villages(),
            'banks' => $this->banks(),
            'packages' => $this->packages(),
        ]);
    }
}
