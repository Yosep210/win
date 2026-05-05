<?php

namespace App\Support\Forms;

use App\Models\City;
use App\Models\District;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Province;
use App\Models\Rank;
use App\Models\User;

class MembershipForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-membershipTable';

    public const EDIT_EVENT = 'membership:edit';

    public const DELETE_EVENT = 'membership:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data membership berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Membership::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.user_id.unique' => 'User sudah memiliki membership.',
            ],
            'fields' => [
                [
                    'name' => 'user_id',
                    'label' => 'User',
                    'type' => 'select',
                    'validation' => ['required', 'exists:users,id', 'unique:memberships,user_id,'.($modelId ?? 'NULL').',id'],
                    'options' => User::query()->pluck('name', 'id')->toArray(),
                    'placeholder' => 'Pilih user',
                ],
                [
                    'name' => 'package_id',
                    'label' => 'Package',
                    'type' => 'select',
                    'validation' => ['nullable', 'exists:packages,id'],
                    'options' => Package::query()->pluck('name', 'id')->toArray(),
                    'placeholder' => 'Pilih package',
                ],
                [
                    'name' => 'rank_id',
                    'label' => 'Rank',
                    'type' => 'select',
                    'validation' => ['nullable', 'exists:ranks,id'],
                    'options' => Rank::query()->pluck('name', 'id')->toArray(),
                    'placeholder' => 'Pilih rank',
                ],
                [
                    'name' => 'as_stockist',
                    'label' => 'Tipe Member',
                    'type' => 'select',
                    'validation' => ['required', 'string', 'max:50'],
                    'options' => [
                        'member' => 'Member',
                        'stockist' => 'Stockist',
                        'distributor' => 'Distributor',
                    ],
                    'default' => 'member',
                ],
                [
                    'name' => 'is_stockist_central',
                    'label' => 'Stockist Central',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => false,
                ],
                [
                    'name' => 'stockist_name',
                    'label' => 'Nama Stockist',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama stockist',
                ],
                [
                    'name' => 'stockist_province_id',
                    'label' => 'Provinsi Stockist',
                    'type' => 'select',
                    'validation' => ['nullable', 'exists:provinces,id'],
                    'options' => Province::query()->pluck('name', 'id')->toArray(),
                    'placeholder' => 'Pilih provinsi',
                ],
                [
                    'name' => 'stockist_city_id',
                    'label' => 'Kota Stockist',
                    'type' => 'select',
                    'validation' => ['nullable', 'exists:cities,id'],
                    'options' => City::query()->pluck('name', 'id')->toArray(),
                    'placeholder' => 'Pilih kota',
                ],
                [
                    'name' => 'stockist_district_id',
                    'label' => 'Kecamatan Stockist',
                    'type' => 'select',
                    'validation' => ['nullable', 'exists:districts,id'],
                    'options' => District::query()->pluck('name', 'id')->toArray(),
                    'placeholder' => 'Pilih kecamatan',
                ],
                [
                    'name' => 'stockist_village',
                    'label' => 'Kelurahan Stockist',
                    'type' => 'text',
                    'validation' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'Masukkan kelurahan',
                ],
                [
                    'name' => 'stockist_address',
                    'label' => 'Alamat Stockist',
                    'type' => 'textarea',
                    'validation' => ['nullable', 'string', 'max:1000'],
                    'placeholder' => 'Masukkan alamat stockist lengkap',
                ],
                [
                    'name' => 'wd_status',
                    'label' => 'Status Withdraw',
                    'type' => 'select',
                    'validation' => ['required', 'string', 'max:50'],
                    'options' => [
                        'manual' => 'Manual',
                        'auto' => 'Otomatis',
                        'blocked' => 'Diblokir',
                    ],
                    'default' => 'manual',
                ],
                [
                    'name' => 'wd_min',
                    'label' => 'Minimum Withdraw',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'placeholder' => 'Masukkan jumlah minimum withdraw',
                    'default' => 0,
                ],
                [
                    'name' => 'is_ro_enabled',
                    'label' => 'RO Enabled',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => false,
                ],
            ],
        ];
    }
}
