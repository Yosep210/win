<?php

use App\Livewire\Bank;
use App\Livewire\City;
use App\Livewire\Country;
use App\Livewire\District;
use App\Livewire\Member;
use App\Livewire\Membership;
use App\Livewire\Package;
use App\Livewire\Permission;
use App\Livewire\Province;
use App\Livewire\Role;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::livewire('roles', Role\Index::class)->name('role.index');
    Route::get('roles/{role}', Role\Show::class)->name('role.show');
    Route::livewire('permissions', Permission\Index::class)->name('permission.index');
    Route::livewire('bank', Bank\Index::class)->name('bank.index');
    Route::livewire('city', City\Index::class)->name('city.index');
    Route::livewire('country', Country\Index::class)->name('country.index');
    Route::livewire('district', District\Index::class)->name('district.index');
    Route::livewire('members/create', Member\Create::class)->name('member.create');
    Route::livewire('members', Member\Index::class)->name('member.index');
    Route::livewire('membership', Membership\Index::class)->name('membership.index');
    Route::livewire('package', Package\Index::class)->name('package.index');
    Route::livewire('province', Province\Index::class)->name('province.index');
});
