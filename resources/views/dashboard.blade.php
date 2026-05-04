@php
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Bank;
use App\Models\City;
use App\Models\Province;
use App\Models\District;
use App\Models\Country;

$userCount = User::userCount();
$roleCount = Role::count();
$permissionCount = Permission::count();
$bankCount = Bank::bankCount();
$countryCount = Country::countryCount();
$provinceCount = Province::provinceCount();
$cityCount = City::cityCount();
$districtCount = District::districtCount();
@endphp
<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-8">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($userCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Users') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($userCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($roleCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Roles') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($roleCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($permissionCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Permissions') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($permissionCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($bankCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Banks') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($bankCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($countryCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Countries') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($countryCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($provinceCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Provinces') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($provinceCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($cityCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Cities') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($cityCount) }}
                    </span>
                </div>
                @endif
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @if (!empty ($districtCount))
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
                    <h2>{{ __('Jumlah Districts') }}</h2>
                    <span class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                        {{ number_format($districtCount) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>