<?php

use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::livewire('roles', Livewire\Role\Index::class)->name('role.index');
    Route::get('roles/{role}', Livewire\Role\Show::class)->name('role.show');
    Route::livewire('permissions', Livewire\Permission\Index::class)->name('permission.index');
    Route::livewire('area', Livewire\Area\Index::class)->name('area.index');
    Route::livewire('bank', Livewire\Bank\Index::class)->name('bank.index');
    Route::livewire('city', Livewire\City\Index::class)->name('city.index');
    Route::livewire('country', Livewire\Country\Index::class)->name('country.index');
    Route::livewire('district', Livewire\District\Index::class)->name('district.index');
    Route::livewire('members/create', Livewire\Member\Create::class)->name('member.create');
    Route::livewire('members', Livewire\Member\Index::class)->name('member.index');
    Route::livewire('membership', Livewire\Membership\Index::class)->name('membership.index');
    Route::livewire('package', Livewire\Package\Index::class)->name('package.index');
    Route::livewire('province', Livewire\Province\Index::class)->name('province.index');
    Route::livewire('rank', Livewire\Rank\Index::class)->name('rank.index');
    Route::livewire('products', Livewire\Product\Index::class)->name('product.index');
    Route::livewire('product-categories', Livewire\ProductCategory\Index::class)->name('product-category.index');
    Route::livewire('product-variants', Livewire\ProductVariant\Index::class)->name('product-variant.index');
    Route::livewire('Supplier', Livewire\Supplier\Index::class)->name('Supplier.index');
    Route::livewire('village', Livewire\Village\Index::class)->name('village.index');
});
