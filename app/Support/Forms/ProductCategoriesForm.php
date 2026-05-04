<?php

namespace App\Support\Forms;

use App\Models\ProductCategories;

class ProductCategoriesForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-productCategoriesTable';

    public const EDIT_EVENT = 'product-category:edit';

    public const DELETE_EVENT = 'product-category:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data kategori produk berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => ProductCategories::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode kategori produk sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'code',
                    'label' => 'Kode Kategori Produk',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:product_categories,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode kategori produk',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Kategori Produk',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama kategori produk',
                ],
                [
                    'name' => 'status',
                    'label' => 'Aktif',
                    'type' => 'checkbox',
                    'validation' => ['boolean'],
                    'default' => true,
                ],
            ],
        ];
    }
}
