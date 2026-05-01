<?php

use App\Livewire\Member\Create;
use App\Livewire\Member\UserTable;
use App\Models\Bank;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Package;
use App\Models\Province;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'member']);
});

function createMemberReferenceData(): array
{
    $country = Country::create([
        'iso' => 'id',
        'name' => 'Indonesia',
        'nice_name' => 'Indonesia',
        'iso3' => 'IDN',
        'num_code' => 360,
        'phone_code' => 62,
        'status' => true,
    ]);

    $province = Province::create([
        'name' => 'Jawa Barat',
    ]);

    $city = City::create([
        'province_id' => $province->id,
        'name' => 'Bandung',
    ]);

    $district = District::create([
        'city_id' => $city->id,
        'name' => 'Coblong',
    ]);

    $village = Village::create([
        'district_id' => $district->id,
        'name' => 'Dago',
    ]);

    $bank = Bank::create([
        'code' => 'BCA',
        'name' => 'Bank Central Asia',
        'status' => true,
    ]);

    $package = Package::create([
        'code' => 'REG',
        'name' => 'Regular Package',
        'is_register' => true,
        'is_active' => true,
    ]);

    return compact('country', 'province', 'city', 'district', 'village', 'bank', 'package');
}

test('admin pages can be rendered', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('member.index'))->assertOk();
    $this->actingAs($admin)->get(route('member.create'))->assertOk();
    $this->actingAs($admin)->get(route('permission.index'))->assertOk();
    $this->actingAs($admin)->get(route('role.index'))->assertOk();
});

test('admin can create member without changing authenticated user', function () {
    $references = createMemberReferenceData();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(Create::class)
        ->set('name', 'Member Baru')
        ->set('username', 'memberbaru')
        ->set('email', 'member@example.com')
        ->set('phone', '08123456789')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('birthDate', '1995-01-01')
        ->set('gender', 'male')
        ->set('idNumber', '3201010101010001')
        ->set('provinceId', $references['province']->id)
        ->set('cityId', $references['city']->id)
        ->set('districtId', $references['district']->id)
        ->set('villageId', $references['village']->id)
        ->set('address', 'Jl. Testing No. 1')
        ->set('bankId', $references['bank']->id)
        ->set('accountNumber', '1234567890')
        ->set('accountName', 'Member Baru')
        ->set('packageId', $references['package']->id)
        ->call('save')
        ->assertHasNoErrors();

    $member = User::where('email', 'member@example.com')->first();

    expect($member)->not->toBeNull();
    expect($member->hasRole('member'))->toBeTrue();
    expect(auth()->id())->toBe($admin->id);
});

test('admin can delete non admin user from member table', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $member = User::factory()->create();
    $member->assignRole('member');

    $this->actingAs($admin);

    Livewire::test(UserTable::class)
        ->call('delete', $member->id);

    $this->assertDatabaseMissing('users', [
        'id' => $member->id,
    ]);
});

test('admin user cannot be deleted from member table', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($superAdmin);

    Livewire::test(UserTable::class)
        ->call('delete', $admin->id);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});
