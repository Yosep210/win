<?php

return [
    'Menu' => [
        [
            'title' => 'Dashboard',
            'icon' => 'home',
            'href' => 'dashboard',
        ],
        [
            'heading' => 'Data Member',
            'icon' => 'users',
            'route' => ['member.*'],
            'sub' => [
                [
                    'title' => 'Create Member',
                    'href' => 'member.create',
                    'role' => 'admin',
                ],
                [
                    'title' => 'Member List',
                    'href' => 'member.index',
                ],
            ],
        ],
        [
            'heading' => 'Admin',
            'icon' => 'lock-closed',
            'route' => ['role.*', 'permission.*'],
            'sub' => [
                [
                    'title' => 'Role',
                    'href' => 'role.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'Permission',
                    'href' => 'permission.index',
                    'role' => 'admin',
                ],
            ],
        ],
        [
            'heading' => 'Master Data',
            'icon' => 'circle-stack',
            'route' => ['bank.*', 'city.*', 'country.*', 'district.*', 'membership.*', 'package.*', 'province.*'],
            'sub' => [
                [
                    'title' => 'Bank',
                    'href' => 'bank.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'City',
                    'href' => 'city.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'Country',
                    'href' => 'country.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'District',
                    'href' => 'district.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'Membership',
                    'href' => 'membership.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'Package',
                    'href' => 'package.index',
                    'role' => 'admin',
                ],
                [
                    'title' => 'Province',
                    'href' => 'province.index',
                    'role' => 'admin',
                ],
            ],
        ],
    ],
];
