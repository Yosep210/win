<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SplFileObject;

class LegacyMasterDataSeeder extends Seeder
{
    /**
     * Import legacy master data from the old SQL dump into the current schema.
     */
    public function run(): void
    {
        $dumpPath = base_path('pengenalan_dbs.sql');

        if (! is_file($dumpPath)) {
            $this->command?->error("Legacy dump not found at [{$dumpPath}].");

            return;
        }

        $areas = [];
        $banks = [];
        $countries = [];
        $provinces = [];
        $cities = [];
        $districts = [];
        $packages = [];
        $settings = [];
        $options = [];
        $ProductCategory = [];
        $products = [];
        $productVariants = [];
        $Supplier = [];
        $rankNames = [];

        $categoryIds = [];
        $usedBankCodes = [];
        $usedProductCodes = [];
        $usedVariantCodes = [];

        $file = new SplFileObject($dumpPath, 'r');

        while (! $file->eof()) {
            $line = trim((string) $file->fgets());

            if (! str_starts_with($line, 'INSERT INTO `jpb_')) {
                continue;
            }

            $parsed = $this->parseInsertLine($line);

            if ($parsed === null) {
                continue;
            }

            $table = $parsed['table'];
            $row = $parsed['row'];

            switch ($table) {
                case 'jpb_area':
                    $areas[] = [
                        'id' => (int) $row['id'],
                        'name' => $this->stringOrEmpty($row['area_name']),
                        'code' => $this->nullableString($row['area_code']) ?? '',
                    ];
                    break;

                case 'jpb_banks':
                    $legacyCode = $this->nullableString($row['kode']);
                    $bankName = $this->stringOrEmpty($row['nama']);
                    $baseCode = Str::lower($legacyCode ?: Str::slug($bankName, '-'));
                    $uniqueCode = $this->makeUniqueCode($baseCode, $usedBankCodes, Str::slug($bankName, '-'));

                    $banks[] = [
                        'id' => (int) $row['id'],
                        'code' => $uniqueCode,
                        'name' => $bankName,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    break;

                case 'jpb_country':
                    $countries[] = [
                        'id' => (int) $row['id'],
                        'iso' => Str::lower($this->stringOrEmpty($row['iso'])),
                        'name' => $this->stringOrEmpty($row['name']),
                        'nice_name' => $this->stringOrEmpty($row['nicename']),
                        'iso3' => $this->nullableString($row['iso3']),
                        'num_code' => $this->nullableInt($row['numcode']),
                        'phone_code' => (int) ($row['phonecode'] ?? 0),
                        'status' => (bool) ($row['status'] ?? false),
                    ];
                    break;

                case 'jpb_province':
                    $provinces[] = [
                        'id' => (int) $row['id'],
                        'countrie_id' => '100', // Indonesia
                        'name' => $this->stringOrEmpty($row['province_name']),
                        'code' => $this->nullableString($row['province_code']),
                    ];
                    break;

                case 'jpb_district':
                    $cities[] = [
                        'id' => (int) $row['id'],
                        'province_id' => (int) $row['province_id'],
                        'name' => $this->stringOrEmpty($row['district_name']),
                        'type' => $this->nullableString($row['district_type']),
                        'code' => $this->nullableString($row['district_code']),
                        'postal_code' => $this->nullableString($row['postal_code']),
                        'external_id' => $this->nullableString($row['id_express']),
                    ];
                    break;

                case 'jpb_subdistrict':
                    $districts[] = [
                        'id' => (int) $row['id'],
                        'city_id' => (int) $row['district_id'],
                        'name' => $this->stringOrEmpty($row['subdistrict_name']),
                        'postal_code' => null,
                        'external_id' => $this->nullableString($row['id_express']),
                    ];
                    break;

                case 'jpb_package':
                    $packages[] = [
                        'code' => $this->stringOrEmpty($row['package']),
                        'name' => $this->stringOrEmpty($row['package_name']),
                        'sort_order' => (int) ($row['package_index'] ?? 0),
                        'package_count' => (int) ($row['package_count'] ?? 1),
                        'bv' => (int) ($row['bv'] ?? 0),
                        'price' => (float) ($row['nominal'] ?? 0),
                        'sponsor_percent' => (float) ($row['sponsor_percent'] ?? 0),
                        'passup_percent' => (float) ($row['passup_percent'] ?? 0),
                        'pairing_percent' => (float) ($row['pairing_percent'] ?? 0),
                        'pairing_nominal' => (float) ($row['pairing_nominal'] ?? 0),
                        'pairing_max' => (int) ($row['pairing_max'] ?? 0),
                        'pairing_point' => (int) ($row['pairing_point'] ?? 0),
                        'reward_point' => (float) ($row['reward_point'] ?? 0),
                        'description' => $this->nullableString($row['description']),
                        'is_register' => (bool) ($row['is_register'] ?? false),
                        'is_order' => (bool) ($row['is_order'] ?? false),
                        'is_active' => (bool) ($row['is_active'] ?? true),
                        'created_at' => $this->legacyTimestamp($row['datecreated']) ?? now(),
                        'updated_at' => $this->legacyTimestamp($row['datemodified']) ?? $this->legacyTimestamp($row['datecreated']) ?? now(),
                    ];
                    break;

                case 'jpb_options':
                    $key = $this->stringOrEmpty($row['name']);
                    $value = is_scalar($row['value']) || $row['value'] === null ? $row['value'] : null;

                    $settings[$key] = [
                        'key' => $key,
                        'value' => $value === null ? null : (string) $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $options[$key] = [
                        'key' => $key,
                        'value' => $value === null ? null : (string) $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    break;

                case 'jpb_product':
                    $type = $this->nullableString($row['type']) ?: 'uncategorized';

                    if (! isset($categoryIds[$type])) {
                        $categoryCode = 'legacy-'.Str::slug($type, '-');
                        $categoryId = count($categoryIds) + 1;

                        $categoryIds[$type] = $categoryId;
                        $ProductCategory[] = [
                            'id' => $categoryId,
                            'code' => $categoryCode,
                            'name' => Str::title(str_replace(['-', '_'], ' ', $type)),
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $productCode = $this->makeUniqueCode(
                        Str::lower($this->nullableString($row['sku']) ?: 'legacy-product-'.(int) $row['id']),
                        $usedProductCodes,
                        (string) $row['id']
                    );

                    $products[] = [
                        'id' => (int) $row['id'],
                        'category_id' => $categoryIds[$type],
                        'code' => $productCode,
                        'name' => $this->stringOrEmpty($row['name']),
                        'description' => $this->nullableString($row['description']),
                        'status' => (bool) ($row['status'] ?? true),
                        'created_at' => $this->legacyTimestamp($row['datecreated']) ?? now(),
                        'updated_at' => $this->legacyTimestamp($row['datemodified']) ?? $this->legacyTimestamp($row['dateupdated']) ?? $this->legacyTimestamp($row['datecreated']) ?? now(),
                    ];

                    $variantName = $this->nullableString($row['varian']) ?: 'Default';
                    $variantCode = $this->makeUniqueCode($productCode.'-default', $usedVariantCodes, (string) $row['id']);

                    $productVariants[] = [
                        'id' => (int) $row['id'],
                        'product_id' => (int) $row['id'],
                        'code' => $variantCode,
                        'name' => $variantName,
                        'price' => (float) (($row['price_member'] ?? 0) ?: ($row['price'] ?? 0)),
                        'bv' => (int) ($row['bv'] ?? 0),
                        'status' => (bool) ($row['status'] ?? true),
                        'created_at' => $this->legacyTimestamp($row['datecreated']) ?? now(),
                        'updated_at' => $this->legacyTimestamp($row['datemodified']) ?? $this->legacyTimestamp($row['dateupdated']) ?? $this->legacyTimestamp($row['datecreated']) ?? now(),
                    ];
                    break;

                case 'jpb_supplier':
                    $Supplier[] = [
                        'name' => $this->stringOrEmpty($row['name']),
                        'email' => $this->nullableString($row['email']),
                        'phone' => $this->nullableString($row['phone']),
                        'address' => $this->nullableString($row['address']),
                        'contact_id' => $this->nullableInt($row['id_contact_kledo'] ?? null),
                        'status' => (bool) ($row['status'] ?? true),
                        'created_at' => $this->legacyTimestamp($row['datecreated']) ?? now(),
                        'updated_at' => $this->legacyTimestamp($row['datemodified']) ?? $this->legacyTimestamp($row['datecreated']) ?? now(),
                    ];
                    break;

                case 'jpb_member':
                    $this->collectRankName($rankNames, $row['rank'] ?? null);
                    break;

                case 'jpb_ranks':
                    $this->collectRankName($rankNames, $row['rank'] ?? null);
                    break;
            }
        }

        $ranks = $this->buildRanks($rankNames);

        DB::transaction(function () use (
            $areas,
            $banks,
            $countries,
            $provinces,
            $cities,
            $districts,
            $packages,
            $settings,
            $options,
            $ranks,
            $ProductCategory,
            $products,
            $productVariants,
            $Supplier,
        ): void {
            $this->upsertChunked('areas', $areas, ['id'], ['name', 'code']);
            $this->upsertChunked('banks', $banks, ['id'], ['code', 'name', 'status', 'updated_at']);
            $this->upsertChunked('countries', $countries, ['id'], ['iso', 'name', 'nice_name', 'iso3', 'num_code', 'phone_code', 'status']);
            $this->upsertChunked('provinces', $provinces, ['id'], ['name', 'code']);
            $this->upsertChunked('cities', $cities, ['id'], ['province_id', 'name', 'type', 'code', 'postal_code', 'external_id']);
            $this->upsertChunked('districts', $districts, ['id'], ['city_id', 'name', 'postal_code', 'external_id']);
            $this->upsertChunked('packages', $packages, ['code'], [
                'name',
                'sort_order',
                'package_count',
                'bv',
                'price',
                'sponsor_percent',
                'passup_percent',
                'pairing_percent',
                'pairing_nominal',
                'pairing_max',
                'pairing_point',
                'reward_point',
                'description',
                'is_register',
                'is_order',
                'is_active',
                'updated_at',
            ]);
            $this->upsertChunked('settings', array_values($settings), ['key'], ['value', 'updated_at']);
            $this->upsertChunked('options', array_values($options), ['key'], ['value', 'updated_at']);
            $this->upsertChunked('ranks', $ranks, ['code'], ['name', 'sort_order', 'is_active', 'updated_at']);
            $this->upsertChunked('product_categories', $ProductCategory, ['id'], ['code', 'name', 'status', 'updated_at']);
            $this->upsertChunked('products', $products, ['id'], ['category_id', 'code', 'name', 'description', 'status', 'updated_at']);
            $this->upsertChunked('product_variants', $productVariants, ['id'], ['product_id', 'code', 'name', 'price', 'bv', 'status', 'updated_at']);
            $this->upsertChunked('Supplier', $Supplier, ['name'], ['email', 'phone', 'address', 'contact_id', 'status', 'updated_at']);
        });

        $this->command?->info('Legacy master data import completed.');
        $this->command?->line('Imported or updated:');
        $this->command?->line('- areas: '.count($areas));
        $this->command?->line('- banks: '.count($banks));
        $this->command?->line('- countries: '.count($countries));
        $this->command?->line('- provinces: '.count($provinces));
        $this->command?->line('- cities: '.count($cities));
        $this->command?->line('- districts: '.count($districts));
        $this->command?->line('- packages: '.count($packages));
        $this->command?->line('- settings: '.count($settings));
        $this->command?->line('- options: '.count($options));
        $this->command?->line('- ranks: '.count($ranks));
        $this->command?->line('- product categories: '.count($ProductCategory));
        $this->command?->line('- products: '.count($products));
        $this->command?->line('- product variants: '.count($productVariants));
        $this->command?->line('- Supplier: '.count($Supplier));
        $this->command?->warn('Village master data was not imported because the legacy dump does not contain a dedicated village table.');
    }

    /**
     * @return array{table:string,row:array<string,mixed>}|null
     */
    private function parseInsertLine(string $line): ?array
    {
        $pattern = '/^INSERT INTO `(?P<table>[^`]+)` \((?P<columns>.+)\) VALUES \((?P<values>.+)\);$/s';

        if (! preg_match($pattern, $line, $matches)) {
            return null;
        }

        $columns = array_map(
            static fn (string $column): string => trim($column, " \t\n\r\0\x0B`"),
            $this->splitSqlList($matches['columns'])
        );

        $values = $this->splitSqlList($matches['values']);

        if (count($columns) !== count($values)) {
            return null;
        }

        $row = [];

        foreach ($columns as $index => $column) {
            $row[$column] = $this->normalizeSqlValue($values[$index]);
        }

        return [
            'table' => $matches['table'],
            'row' => $row,
        ];
    }

    /**
     * @return list<string>
     */
    private function splitSqlList(string $input): array
    {
        $parts = [];
        $buffer = '';
        $inString = false;
        $escaped = false;
        $length = strlen($input);

        for ($index = 0; $index < $length; $index++) {
            $char = $input[$index];

            if ($escaped) {
                $buffer .= $char;
                $escaped = false;

                continue;
            }

            if ($char === '\\' && $inString) {
                $buffer .= $char;
                $escaped = true;

                continue;
            }

            if ($char === "'") {
                $buffer .= $char;
                $inString = ! $inString;

                continue;
            }

            if ($char === ',' && ! $inString) {
                $parts[] = trim($buffer);
                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $parts[] = trim($buffer);
        }

        return $parts;
    }

    private function normalizeSqlValue(string $value): mixed
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (preg_match("/^'(.*)'$/s", $value, $matches) === 1) {
            return str_replace(
                ["\\'", '\\\\', '\r', '\n'],
                ["'", '\\', "\r", "\n"],
                $matches[1]
            );
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }

    private function stringOrEmpty(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function legacyTimestamp(mixed $value): ?string
    {
        $timestamp = $this->nullableString($value);

        if ($timestamp === null || str_starts_with($timestamp, '0000-00-00')) {
            return null;
        }

        return $timestamp;
    }

    private function makeUniqueCode(string $baseCode, array &$usedCodes, string $suffix): string
    {
        $baseCode = trim($baseCode) !== '' ? trim($baseCode) : 'legacy';
        $code = $baseCode;

        while (isset($usedCodes[$code])) {
            $code = $baseCode.'-'.Str::slug($suffix, '-');

            if (! isset($usedCodes[$code])) {
                break;
            }

            $code .= '-'.count($usedCodes);
        }

        $usedCodes[$code] = true;

        return $code;
    }

    private function collectRankName(array &$rankNames, mixed $rank): void
    {
        $name = $this->nullableString($rank);

        if ($name === null) {
            return;
        }

        $normalized = Str::lower($name);

        if (! array_key_exists($normalized, $rankNames)) {
            $rankNames[$normalized] = $name;
        }
    }

    /**
     * @param  array<string,string>  $rankNames
     * @return list<array<string,mixed>>
     */
    private function buildRanks(array $rankNames): array
    {
        $rows = [];
        $sortOrder = 1;

        foreach ($rankNames as $name) {
            $rows[] = [
                'code' => Str::slug($name, '-'),
                'name' => Str::title(str_replace(['_', '-'], ' ', $name)),
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array<string,mixed>>  $rows
     * @param  list<string>  $uniqueBy
     * @param  list<string>  $updateColumns
     */
    private function upsertChunked(string $table, array $rows, array $uniqueBy, array $updateColumns): void
    {
        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table($table)->upsert($chunk, $uniqueBy, $updateColumns);
        }
    }
}
