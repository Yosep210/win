<?php

namespace App\Livewire\Member\Concerns;

use App\Models;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait InteractsWithMemberFormData
{
    protected function defaultCountryId(): ?int
    {
        return Models\Country::query()
            ->where('iso', 'id')
            ->where('status', true)
            ->value('id')
            ?? Models\Country::query()->where('status', true)->value('id');
    }

    protected function provinces(): Collection
    {
        if (! $this->countryId) {
            return new Collection;
        }

        return Models\Province::query()
            ->where('country_id', $this->countryId)
            ->orderBy('name')
            ->get();
    }

    protected function cities(): Collection
    {
        if (! $this->provinceId) {
            return new Collection;
        }

        return Models\City::query()
            ->where('province_id', $this->provinceId)
            ->orderBy('name')
            ->get();
    }

    protected function regencies(): Collection
    {
        if (! $this->cityId) {
            return new Collection;
        }

        return Models\Regency::query()
            ->where('city_id', $this->cityId)
            ->orderBy('name')
            ->get();
    }

    protected function villages(): Collection
    {
        if (! $this->regencyId) {
            return new Collection;
        }

        return Models\Village::query()
            ->where('regency_id', $this->regencyId)
            ->orderBy('name')
            ->get();
    }

    protected function banks(): Collection
    {
        return Models\Bank::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    protected function packages(): Collection
    {
        return Models\Package::query()
            ->where('is_active', true)
            ->where('is_register', true)
            ->orderBy('name')
            ->get();
    }

    protected function hasProvinceOptions(): bool
    {
        return $this->countryId
            ? Models\Province::query()->where('country_id', $this->countryId)->exists()
            : false;
    }

    protected function hasBankOptions(): bool
    {
        return Models\Bank::query()->where('status', true)->exists();
    }

    protected function hasPackageOptions(): bool
    {
        return Models\Package::query()
            ->where('is_active', true)
            ->where('is_register', true)
            ->exists();
    }

    protected function countryExistsRule(): Exists
    {
        return Rule::exists('countries', 'id')
            ->where(fn ($query) => $query->where('status', true));
    }

    protected function provinceExistsRule(): Exists
    {
        return Rule::exists('provinces', 'id')
            ->where(fn ($query) => $this->countryId
                ? $query->where('country_id', $this->countryId)
                : $query->whereRaw('1 = 0'));
    }

    protected function cityExistsRule(): Exists
    {
        return Rule::exists('cities', 'id')
            ->where(fn ($query) => $this->provinceId
                ? $query->where('province_id', $this->provinceId)
                : $query->whereRaw('1 = 0'));
    }

    protected function regencyExistsRule(): Exists
    {
        return Rule::exists('regencies', 'id')
            ->where(fn ($query) => $this->cityId
                ? $query->where('city_id', $this->cityId)
                : $query->whereRaw('1 = 0'));
    }

    protected function villageExistsRule(): Exists
    {
        return Rule::exists('villages', 'id')
            ->where(fn ($query) => $this->regencyId
                ? $query->where('regency_id', $this->regencyId)
                : $query->whereRaw('1 = 0'));
    }

    protected function bankExistsRule(): Exists
    {
        return Rule::exists('banks', 'id')
            ->where(fn ($query) => $query->where('status', true));
    }

    protected function packageExistsRule(): Exists
    {
        return Rule::exists('packages', 'id')
            ->where(fn ($query) => $query
                ->where('is_active', true)
                ->where('is_register', true));
    }
}
