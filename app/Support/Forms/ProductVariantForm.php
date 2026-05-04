<?php

namespace App\Support\Forms;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantForm
{
    public const REFRESH_EVENT = 'pg:eventRefresh-productVariantTable';

    public const EDIT_EVENT = 'product-variant:edit';

    public const DELETE_EVENT = 'product-variant:delete';

    public static function make(
        string $title,
        ?int $modelId = null,
        string $successMessage = 'Data product variant berhasil disimpan.',
    ): array {
        return [
            'title' => $title,
            'modelClass' => ProductVariant::class,
            'modelId' => $modelId,
            'refreshEvent' => self::REFRESH_EVENT,
            'successMessage' => $successMessage,
            'validationMessages' => [
                'data.code.unique' => 'Kode varian produk sudah digunakan.',
            ],
            'fields' => [
                [
                    'name' => 'product_id',
                    'label' => 'Produk',
                    'type' => 'select',
                    'options' => Product::query()->select('id', 'name')->get()->toArray(),
                    'validation' => ['required', 'exists:products,id'],
                ],
                [
                    'name' => 'code',
                    'label' => 'Kode Varian',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255', 'unique:product_variants,code,'.($modelId ?? 'NULL').',id'],
                    'placeholder' => 'Masukkan kode varian',
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Varian',
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:255'],
                    'placeholder' => 'Masukkan nama varian',
                ],
                [
                    'name' => 'price',
                    'label' => 'Harga',
                    'type' => 'number',
                    'validation' => ['required', 'numeric', 'min:0'],
                    'default' => 0,
                ],
                [
                    'name' => 'bv',
                    'label' => 'BV',
                    'type' => 'number',
                    'validation' => ['required', 'integer', 'min:0'],
                    'default' => 0,
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
