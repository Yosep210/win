<?php

namespace App\Livewire\Member;

use App\Concerns\ProfileValidationRules;
use App\Models;
use App\Models\Bank;
use App\Models\City;
use App\Models\District;
use App\Models\Package;
use App\Models\Province;
use App\Models\User;
use App\Models\Village;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use ProfileValidationRules;

    public User $user;

    // Properti Formulir
    public $username;

    public $name;

    public $email;

    public $phone;

    public $birthDate;

    public $gender;

    public $idNumber;

    public $npwp;

    public $countryId;

    public $provinceId;

    public $cityId;

    public $districtId;

    public $villageId;

    public $address;

    public $bankId;

    public $accountNumber;

    public $accountName;

    public $packageId;

    public $asStockist;

    public $isStockistCentral;

    public $isActive;

    public function mount(User $user): void
    {
        $this->user = $user->load(['profile', 'membership.package', 'bankAccounts', 'network']);

        // Inisialisasi properti dari model
        $this->username = $user->username;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->isActive = $user->status === 'active';

        if ($profile = $user->profile) {
            $this->birthDate = optional($profile->birth_date)->format('Y-m-d');
            $this->gender = $profile->gender;
            $this->idNumber = $profile->id_number;
            $this->npwp = $profile->npwp;
            $this->provinceId = $profile->province_id;
            $this->cityId = $profile->city_id;
            $this->districtId = $profile->district_id;
            $this->villageId = $profile->village_id;
            $this->address = $profile->address;
            $this->countryId = $profile->country_id;
        }

        if ($bankAccount = $user->bankAccounts->first()) {
            $this->bankId = $bankAccount->bank_id;
            $this->accountNumber = $bankAccount->account_number;
            $this->accountName = $bankAccount->account_name;
        }

        if ($membership = $user->membership) {
            $this->packageId = $membership->package_id;
            $this->asStockist = $membership->as_stockist ?? 'member';
            $this->isStockistCentral = (bool) $membership->is_stockist_central;
        }

        $this->countryId ??= Models\Country::query()
            ->where('iso', 'id')
            ->value('id') ?? Models\Country::query()->value('id');
        $this->asStockist ??= 'member';
        $this->isStockistCentral ??= false;
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

    protected function formRules(): array
    {
        $hasProvinceOptions = Province::query()->exists();
        $hasBankOptions = Bank::query()->where('status', true)->exists();
        $hasPackageOptions = Package::query()
            ->where('is_active', true)
            ->where('is_register', true)
            ->exists();

        return [
            ...$this->profileRules($this->user->id),
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->user->id)],
            'birthDate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'idNumber' => ['required', 'string', 'max:255', Rule::unique('user_profiles', 'id_number')->ignore($this->user->profile?->id)],
            'npwp' => ['nullable', 'string', 'max:255', Rule::unique('user_profiles', 'npwp')->ignore($this->user->profile?->id)],
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
            'isStockistCentral' => ['boolean'],
            'isActive' => ['boolean'],
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

    public function save(): void
    {
        $validated = $this->validate($this->formRules(), [], $this->validationAttributes());
        $phone = $this->normalizePhone($validated['phone']);

        if ($phone === '') {
            $this->addError('phone', 'Phone number is required.');

            return;
        }

        if (User::query()->where('phone', $phone)->whereKeyNot($this->user->id)->exists()) {
            $this->addError('phone', 'The phone has already been taken.');

            return;
        }

        DB::transaction(function () use ($validated, $phone): void {
            $this->user->update([
                'name' => $validated['name'],
                'username' => Str::lower($validated['username']),
                'email' => Str::lower($validated['email']),
                'phone' => $phone,
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
                    'district_id' => $validated['districtId'] ?: null,
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
                $existingBankAccount = $this->user->bankAccounts()->first();

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
                $this->user->bankAccounts()->delete();
            }
        });

        $this->user->refresh()->load(['profile', 'membership.package', 'bankAccounts', 'network']);

        Flux::toast(variant: 'success', text: 'Member updated successfully.');
    }

    public function render(): View
    {
        return view('livewire.member.edit', [
            'provinces' => Province::all(),
            'cities' => $this->provinceId ? City::where('province_id', $this->provinceId)->get() : collect(),
            'districts' => $this->cityId ? District::where('city_id', $this->cityId)->get() : collect(),
            'villages' => $this->districtId ? Village::where('district_id', $this->districtId)->get() : collect(),
            'banks' => Bank::all(),
            'packages' => Package::all(),
        ]);
    }
}
