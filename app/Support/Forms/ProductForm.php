<?php

namespace App\Support\Forms;

use App\Models\Product;
use App\Models\ProductCategories;

class ProductForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-productTable';

    public const EDIT_EVENT = 'product:edit';

    public const DELETE_EVENT = 'product:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data product berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => Product::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode produk sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'category_id',
                    'label' => 'Kategori Produk',
                    'type' => 'select',
                    'options' => ProductCategories::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['nullable', 'exists:product_categories,id'],
                ],
                [
                    'name' => 'code',
                    'label' => 'Kode Produk',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:products,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode produk',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Produk',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama produk',
                ],
                [
                    'name' => 'description',
                    'label' => 'Deskripsi',
                    'type' => 'textarea',
                    'validation' => ['nullable', 'string'],
                    'placeholder' => 'Masukkan deskripsi produk',
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
