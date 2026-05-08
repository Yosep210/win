<?php

use App\Livewire\Member\Create;
use App\Livewire\Member\Edit;
use App\Livewire\Member\UserTable;
use App\Models\Bank;
use App\Models\City;
use App\Models\Country;
use App\Models\Package;
use App\Models\Province;
use App\Models\Regency;
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
        'numcode' => 360,
        'phone_code' => 62,
        'status' => true,
    ]);

    $province = Province::create([
        'country_id' => $country->id,
        'name' => 'Jawa Barat',
    ]);

    $city = City::create([
        'province_id' => $province->id,
        'name' => 'Bandung',
    ]);

    $regency = Regency::create([
        'city_id' => $city->id,
        'name' => 'Coblong',
    ]);

    $village = Village::create([
        'regency_id' => $regency->id,
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

    return compact('country', 'province', 'city', 'regency', 'village', 'bank', 'package');
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
        ->set('regencyId', $references['regency']->id)
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

test('member creation rejects invalid wilayah hierarchy', function () {
    $references = createMemberReferenceData();

    $otherProvince = Province::create([
        'country_id' => $references['country']->id,
        'name' => 'Jawa Tengah',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(Create::class)
        ->set('name', 'Member Salah Wilayah')
        ->set('username', 'membersalah')
        ->set('email', 'salah@example.com')
        ->set('phone', '08111111111')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('birthDate', '1995-01-01')
        ->set('gender', 'male')
        ->set('idNumber', '3201010101010002')
        ->set('provinceId', $otherProvince->id)
        ->set('cityId', $references['city']->id)
        ->set('regencyId', $references['regency']->id)
        ->set('villageId', $references['village']->id)
        ->set('address', 'Jl. Tidak Sinkron')
        ->set('bankId', $references['bank']->id)
        ->set('accountNumber', '1234567891')
        ->set('accountName', 'Member Salah Wilayah')
        ->set('packageId', $references['package']->id)
        ->call('save')
        ->assertHasErrors(['cityId']);
});

test('member edit only accepts linked wilayah hierarchy', function () {
    $references = createMemberReferenceData();

    $otherProvince = Province::create([
        'country_id' => $references['country']->id,
        'name' => 'Jawa Tengah',
    ]);

    $member = User::factory()->create([
        'status' => 'active',
    ]);
    $member->assignRole('member');
    $member->profile()->create([
        'gender' => 'male',
        'birth_date' => '1990-01-01',
        'id_number' => '3201010101010099',
        'country_id' => $references['country']->id,
        'province_id' => $references['province']->id,
        'city_id' => $references['city']->id,
        'regency_id' => $references['regency']->id,
        'village_id' => $references['village']->id,
        'address' => 'Alamat awal',
    ]);
    $member->membership()->create([
        'package_id' => $references['package']->id,
        'as_stockist' => 'member',
        'joined_at' => now(),
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(Edit::class, ['user' => $member])
        ->set('username', 'memberedit')
        ->set('name', 'Member Edit')
        ->set('email', 'memberedit@example.com')
        ->set('phone', '08122222222')
        ->set('birthDate', '1990-01-01')
        ->set('gender', 'male')
        ->set('idNumber', '3201010101010099')
        ->set('provinceId', $otherProvince->id)
        ->set('cityId', $references['city']->id)
        ->set('regencyId', $references['regency']->id)
        ->set('villageId', $references['village']->id)
        ->set('address', 'Alamat baru')
        ->set('packageId', $references['package']->id)
        ->set('asStockist', 'member')
        ->call('save')
        ->assertHasErrors(['cityId']);
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
