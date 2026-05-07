<?php

namespace App\Livewire\Member;

use App\Models\Bank;
use App\Models\City;
use App\Models\District;
use App\Models\Package;
use App\Models\Province;
use App\Models\User;
use App\Models\Village;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Edit extends Component
{
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
    }

    public function save()
    {
        // Logika validasi dan update akan diletakkan di sini
        // ...
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
