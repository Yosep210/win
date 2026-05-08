<?php

namespace App\Livewire\Member;

use App\Concerns\ProfileValidationRules;
use App\Livewire\Member\Concerns\InteractsWithMemberFormData;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use InteractsWithMemberFormData;
    use ProfileValidationRules;

    public User $user;

    public string $username = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

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

    public bool $isActive = true;

    public function mount(User $user): void
    {
        $this->user = $user->load(['profile', 'membership.package', 'bankAccounts', 'network']);

        $this->username = (string) ($user->username ?? '');
        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->phone = (string) ($user->phone ?? '');
        $this->isActive = $user->status === 'active';

        if ($profile = $user->profile) {
            $this->birthDate = optional($profile->birth_date)->format('Y-m-d');
            $this->gender = (string) ($profile->gender ?? '');
            $this->idNumber = (string) ($profile->id_number ?? '');
            $this->npwp = (string) ($profile->npwp ?? '');
            $this->provinceId = $profile->province_id;
            $this->cityId = $profile->city_id;
            $this->regencyId = $profile->regency_id;
            $this->villageId = $profile->village_id;
            $this->address = (string) ($profile->address ?? '');
            $this->countryId = $profile->country_id;
        }

        if ($bankAccount = $user->bankAccounts->sortByDesc('is_primary')->first()) {
            $this->bankId = $bankAccount->bank_id;
            $this->accountNumber = (string) ($bankAccount->account_number ?? '');
            $this->accountName = (string) ($bankAccount->account_name ?? '');
        }

        if ($membership = $user->membership) {
            $this->packageId = $membership->package_id;
            $this->asStockist = $membership->as_stockist ?? 'member';
            $this->isStockistCentral = (bool) $membership->is_stockist_central;
        }

        $this->countryId ??= $this->defaultCountryId();
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

    protected function formRules(): array
    {
        $hasProvinceOptions = $this->hasProvinceOptions();
        $hasBankOptions = $this->hasBankOptions();
        $hasPackageOptions = $this->hasPackageOptions();

        return [
            ...$this->profileRules($this->user->id),
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->user->id)],
            'birthDate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'idNumber' => ['required', 'string', 'max:255', Rule::unique('user_profiles', 'id_number')->ignore($this->user->profile?->id)],
            'npwp' => ['nullable', 'string', 'max:255', Rule::unique('user_profiles', 'npwp')->ignore($this->user->profile?->id)],
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
            'isStockistCentral' => ['boolean'],
            'isActive' => ['boolean'],
        ];
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
    }

    public function save(): void
    {
        $this->normalizeFormState();

        $validated = $this->validate($this->formRules(), [], $this->validationAttributes());

        DB::transaction(function () use ($validated): void {
            $this->user->update([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $this->isActive ? 'active' : 'inactive',
            ]);

            $this->user->profile()->updateOrCreate(
                ['user_id' => $this->user->id],
                [
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
                ]
            );

            $this->user->membership()->updateOrCreate(
                ['user_id' => $this->user->id],
                [
                    'package_id' => $validated['packageId'] ?: null,
                    'as_stockist' => $validated['asStockist'],
                    'is_stockist_central' => $this->isStockistCentral,
                    'stockist_name' => $validated['asStockist'] === 'stockist' ? $validated['name'] : null,
                    'joined_at' => $this->user->membership?->joined_at ?? now(),
                    'stockist_at' => $validated['asStockist'] === 'stockist'
                        ? ($this->user->membership?->stockist_at ?? now())
                        : null,
                ]
            );

            if ($validated['bankId'] && $validated['accountNumber'] && $validated['accountName']) {
                $existingBankAccount = $this->user->bankAccounts()
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->first();

                if ($existingBankAccount) {
                    $existingBankAccount->update([
                        'bank_id' => $validated['bankId'],
                        'account_number' => $validated['accountNumber'],
                        'account_name' => Str::upper($validated['accountName']),
                        'is_primary' => true,
                    ]);
                } else {
                    $this->user->bankAccounts()->create([
                        'bank_id' => $validated['bankId'],
                        'account_number' => $validated['accountNumber'],
                        'account_name' => Str::upper($validated['accountName']),
                        'is_primary' => true,
                    ]);
                }
            } else {
                $this->user->bankAccounts()
                    ->where('is_primary', true)
                    ->delete();
            }
        });

        $this->user->refresh()->load(['profile', 'membership.package', 'bankAccounts', 'network']);

        Flux::toast(variant: 'success', text: 'Member updated successfully.');
    }

    public function render(): View
    {
        return view('livewire.member.edit', [
            'provinces' => $this->provinces(),
            'cities' => $this->cities(),
            'regencies' => $this->regencies(),
            'villages' => $this->villages(),
            'banks' => $this->banks(),
            'packages' => $this->packages(),
        ]);
    }
}
