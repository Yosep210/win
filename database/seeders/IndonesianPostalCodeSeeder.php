<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class IndonesianPostalCodeSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $csvPath = base_path('kode_pos_kab_kota_indonesia.csv');

        if (! is_file($csvPath)) {
            $this->command?->error("File kode pos tidak ditemukan: {$csvPath}");

            return;
        }

        $villageIndex = $this->buildVillageIndex();

        if ($villageIndex === []) {
            $this->command?->warn('Data village/regency/city/province belum tersedia. Jalankan IndonesianWilayahSeeder terlebih dahulu.');

            return;
        }

        $file = new SplFileObject($csvPath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $header = null;
        $updates = [];
        $processed = 0;
        $matched = 0;
        $skipped = 0;
        $unmatchedSamples = [];
        $preFilledCount = DB::table('villages')->whereNotNull('postal_code')->count();

        foreach ($file as $row) {
            if (! is_array($row) || $row === [null] || $row === false) {
                continue;
            }

            if ($header === null) {
                $header = $row;

                continue;
            }

            if (count($row) !== count($header)) {
                $skipped++;

                continue;
            }

            $record = array_combine($header, $row);

            if (! is_array($record)) {
                $skipped++;

                continue;
            }

            $processed++;

            $postalCode = $this->normalizePostalCode($record['kode_pos'] ?? null);

            if ($postalCode === null) {
                $skipped++;

                continue;
            }

            $villageId = $this->matchVillageId($record, $villageIndex);

            if ($villageId === null) {
                $skipped++;
                $this->collectUnmatchedSample($unmatchedSamples, $record);

                continue;
            }

            $updates[$villageId] = [
                'id' => $villageId,
                'postal_code' => $postalCode,
            ];

            $matched++;
        }

        foreach (array_chunk(array_values($updates), 1000) as $chunk) {
            foreach ($chunk as &$row) {
                DB::table('villages')
                    ->where('id', $row['id'])
                    ->update(['postal_code' => $row['postal_code']]);
            }
        }

        $postFilledCount = DB::table('villages')->whereNotNull('postal_code')->count();

        $this->command?->info("Postal code processed: {$processed}");
        $this->command?->info('Postal code matched to villages: '.count($updates));
        $this->command?->line("Matched rows: {$matched}");
        $this->command?->line("Skipped rows: {$skipped}");
        $this->command?->line("Village postal_code before: {$preFilledCount}");
        $this->command?->line("Village postal_code after: {$postFilledCount}");

        if ($unmatchedSamples !== []) {
            $this->command?->warn('Contoh data yang belum cocok:');

            foreach ($unmatchedSamples as $sample) {
                $this->command?->line(
                    '- '.$sample['province'].' | '.$sample['city'].' | '.$sample['regency'].' | '.$sample['village'].' | '.$sample['postal_code']
                );
            }
        }
    }

    /**
     * @return array<string, int>
     */
    private function buildVillageIndex(): array
    {
        $rows = DB::table('villages')
            ->join('regencies', 'regencies.id', '=', 'villages.regency_id')
            ->join('cities', 'cities.id', '=', 'regencies.city_id')
            ->join('provinces', 'provinces.id', '=', 'cities.province_id')
            ->selectRaw(
                'villages.id, REPLACE(UPPER(villages.name), \' \', \'\') as village_name, REPLACE(UPPER(regencies.name), \' \', \'\') as regency_name, REPLACE(UPPER(cities.name), \' \', \'\') as city_name, REPLACE(UPPER(provinces.name), \' \', \'\') as province_name'
            )
            ->get();

        $index = [];

        foreach ($rows as $row) {
            $key = $this->makeHierarchyKey(
                $row->province_name,
                $row->city_name,
                $row->regency_name,
                $row->village_name,
            );

            if ($key !== null) {
                $index[$key] = (int) $row->id;
            }
        }

        return $index;
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, int>  $villageIndex
     */
    private function matchVillageId(array $record, array $villageIndex): ?int
    {
        $provinceNames = $this->uniqueCandidateNames([
            $record['nama_kemendagri_provinsi'] ?? null,
            $record['nama_bps_provinsi'] ?? null,
        ]);

        $cityNames = $this->uniqueCandidateNames([
            $record['nama_kabupaten_kota'] ?? null,
        ], true);

        $regencyNames = $this->uniqueCandidateNames([
            $record['kemendagri_nama_kecamatan'] ?? null,
            $record['bps_nama_kecamatan'] ?? null,
        ], true);

        $villageNames = $this->uniqueCandidateNames([
            $record['kemendagri_nama_desa_kelurahan'] ?? null,
            $record['bps_nama_desa_kelurahan'] ?? null,
        ], true);

        foreach ($provinceNames as $provinceName) {
            foreach ($cityNames as $cityName) {
                foreach ($regencyNames as $regencyName) {
                    foreach ($villageNames as $villageName) {
                        $key = $this->makeHierarchyKey($provinceName, $cityName, $regencyName, $villageName);

                        if ($key !== null && isset($villageIndex[$key])) {
                            return $villageIndex[$key];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  list<mixed>  $values
     * @return list<string>
     */
    private function uniqueCandidateNames(array $values, bool $withLooseVariants = false): array
    {
        $candidates = [];

        foreach ($values as $value) {
            $normalized = $this->normalizeName($value);

            if ($normalized === '') {
                continue;
            }

            $candidates[$normalized] = $normalized;

            if (! $withLooseVariants) {
                continue;
            }

            foreach ($this->looseVariants($normalized) as $variant) {
                if ($variant !== '') {
                    $candidates[$variant] = $variant;
                }
            }
        }

        return array_values($candidates);
    }

    /**
     * @return list<string>
     */
    private function looseVariants(string $value): array
    {
        $variants = [$value];

        $replacements = [
            '/\bKABUPATEN\b/' => '',
            '/\bKOTA\b/' => '',
            '/\bADM\b/' => '',
            '/\bADMINISTRASI\b/' => '',
            '/\bKECAMATAN\b/' => '',
            '/\bKELURAHAN\b/' => '',
            '/\bDESA\b/' => '',
            '/\bGAMPONG\b/' => '',
            '/\bKAMPUNG\b/' => '',
            '/\bNAGARI\b/' => '',
            '/\bDUSUN\b/' => '',
            '/\bTHE\b/' => '',
        ];

        $current = $value;

        foreach ($replacements as $pattern => $replacement) {
            $current = preg_replace($pattern, $replacement, $current) ?? $current;
        }

        $current = preg_replace('/\s+/', ' ', trim($current)) ?? trim($current);

        if ($current !== '') {
            $variants[] = $current;
        }

        $collapsed = str_replace(' ', '', $current);

        if ($collapsed !== '') {
            $variants[] = $collapsed;
        }

        // Add variant without apostrophes
        $currentNoApostrophe = str_replace("'", '', $current);
        $currentNoApostrophe = preg_replace('/\s+/', ' ', trim($currentNoApostrophe)) ?? trim($currentNoApostrophe);

        if ($currentNoApostrophe !== '' && $currentNoApostrophe !== $current) {
            $variants[] = $currentNoApostrophe;

            $collapsedNoApostrophe = str_replace(' ', '', $currentNoApostrophe);

            if ($collapsedNoApostrophe !== '') {
                $variants[] = $collapsedNoApostrophe;
            }
        }

        return array_values(array_unique($variants));
    }

    private function makeHierarchyKey(
        mixed $provinceName,
        mixed $cityName,
        mixed $regencyName,
        mixed $villageName,
    ): ?string {
        $normalizedProvince = str_replace(' ', '', $this->normalizeName($provinceName));
        $normalizedCity = str_replace(' ', '', $this->normalizeName($cityName));
        $normalizedRegency = str_replace(' ', '', $this->normalizeName($regencyName));
        $normalizedVillage = str_replace(' ', '', $this->normalizeName($villageName));

        if ($normalizedProvince === '' || $normalizedCity === '' || $normalizedRegency === '' || $normalizedVillage === '') {
            return null;
        }

        return implode('|', [
            $normalizedProvince,
            $normalizedCity,
            $normalizedRegency,
            $normalizedVillage,
        ]);
    }

    private function normalizeName(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) $value));

        if ($value === '') {
            return '';
        }

        $value = str_replace(
            ["'", '`', '.', ',', '(', ')', '/', '\\', '-'],
            ' ',
            $value
        );

        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    private function normalizePostalCode(mixed $value): ?string
    {
        $value = preg_replace('/\D+/', '', trim((string) $value)) ?? '';

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<int, array<string, string>>  $samples
     * @param  array<string, mixed>  $record
     */
    private function collectUnmatchedSample(array &$samples, array $record): void
    {
        if (count($samples) >= 10) {
            return;
        }

        $samples[] = [
            'province' => (string) ($record['nama_kemendagri_provinsi'] ?? $record['nama_bps_provinsi'] ?? '-'),
            'city' => (string) ($record['nama_kabupaten_kota'] ?? '-'),
            'regency' => (string) ($record['kemendagri_nama_kecamatan'] ?? $record['bps_nama_kecamatan'] ?? '-'),
            'village' => (string) ($record['kemendagri_nama_desa_kelurahan'] ?? $record['bps_nama_desa_kelurahan'] ?? '-'),
            'postal_code' => (string) ($record['kode_pos'] ?? '-'),
        ];
    }
}
