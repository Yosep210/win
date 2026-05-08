<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class IndonesianWilayahSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $indonesia = Country::query()->where('iso', 'id')->first();

        if (! $indonesia) {
            $this->command?->error('Data negara Indonesia tidak ditemukan.');

            return;
        }

        $provincePath = base_path('provinces.json');

        if (! File::exists($provincePath)) {
            $this->command?->error('File provinces.json tidak ditemukan.');

            return;
        }

        $provinces = json_decode(File::get($provincePath), true);

        if (! is_array($provinces) || $provinces === []) {
            $this->command?->error('File provinces.json kosong atau formatnya tidak valid.');

            return;
        }

        $baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

        $this->seedHierarchy($provinces, $indonesia->id, $baseUrl);
    }

    private function seedHierarchy(array $provinces, int $countryId, string $baseUrl): void
    {
        $provinceCount = 0;

        foreach ($provinces as $province) {
            $provinceApiId = trim((string) data_get($province, 'id', ''));
            $provinceName = $this->normalizeName(data_get($province, 'name'));

            if ($provinceApiId === '' || $provinceName === '') {
                continue;
            }

            Province::query()->upsert([[
                'country_id' => $countryId,
                'name' => $provinceName,
            ]], ['country_id', 'name'], ['name']);

            $localProvinceId = Province::query()
                ->where('country_id', $countryId)
                ->where('name', $provinceName)
                ->value('id');

            if (! $localProvinceId) {
                continue;
            }

            $provinceCount++;

            $this->command?->line('Processing province: '.$provinceName);

            $cities = $this->fetchJson("{$baseUrl}/regencies/{$provinceApiId}.json");

            if ($cities === null) {
                continue;
            }

            $cityRows = collect($cities)
                ->map(function (array $city) use ($localProvinceId): ?array {
                    $cityName = $this->normalizeName(data_get($city, 'name'));

                    if ($cityName === '') {
                        return null;
                    }

                    return [
                        'province_id' => $localProvinceId,
                        'name' => $cityName,
                        'type' => $this->resolveCityType($cityName),
                    ];
                })
                ->filter()
                ->values()
                ->all();

            if ($cityRows === []) {
                continue;
            }

            City::query()->upsert($cityRows, ['province_id', 'name'], ['type']);

            $localCities = City::query()
                ->where('province_id', $localProvinceId)
                ->get()
                ->keyBy('name');

            foreach ($cities as $city) {
                $cityApiId = trim((string) data_get($city, 'id', ''));
                $cityName = $this->normalizeName(data_get($city, 'name'));

                if ($cityApiId === '' || $cityName === '') {
                    continue;
                }

                $localCityId = $localCities->get($cityName)?->id;

                if (! $localCityId) {
                    continue;
                }

                $regencies = $this->fetchJson("{$baseUrl}/districts/{$cityApiId}.json");

                if ($regencies === null) {
                    continue;
                }

                $regencyRows = collect($regencies)
                    ->map(function (array $regency) use ($localCityId): ?array {
                        $regencyName = $this->normalizeName(data_get($regency, 'name'));

                        if ($regencyName === '') {
                            return null;
                        }

                        return [
                            'city_id' => $localCityId,
                            'name' => $regencyName,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($regencyRows === []) {
                    continue;
                }

                Regency::query()->upsert($regencyRows, ['city_id', 'name'], ['name']);

                $localRegencies = Regency::query()
                    ->where('city_id', $localCityId)
                    ->get()
                    ->keyBy('name');

                foreach ($regencies as $regency) {
                    $regencyApiId = trim((string) data_get($regency, 'id', ''));
                    $regencyName = $this->normalizeName(data_get($regency, 'name'));

                    if ($regencyApiId === '' || $regencyName === '') {
                        continue;
                    }

                    $localRegencyId = $localRegencies->get($regencyName)?->id;

                    if (! $localRegencyId) {
                        continue;
                    }

                    $villages = $this->fetchJson("{$baseUrl}/villages/{$regencyApiId}.json");

                    if ($villages === null || $villages === []) {
                        continue;
                    }

                    $villageRows = collect($villages)
                        ->map(function (array $village) use ($localRegencyId): ?array {
                            $villageName = $this->normalizeName(data_get($village, 'name'));

                            if ($villageName === '') {
                                return null;
                            }

                            return [
                                'regency_id' => $localRegencyId,
                                'name' => $villageName,
                                'postal_code' => null,
                            ];
                        })
                        ->filter()
                        ->values()
                        ->all();

                    if ($villageRows === []) {
                        continue;
                    }

                    Village::query()->upsert($villageRows, ['regency_id', 'name'], ['postal_code']);
                }
            }
        }

        $this->command?->info("Provinces imported from provinces.json: {$provinceCount}");
        $this->command?->info('Indonesian wilayah import selesai.');
        $this->command?->warn('Village postal_code dibiarkan null karena source API tidak mengirim kode pos.');
    }

    private function fetchJson(string $url): ?array
    {
        try {
            $response = Http::acceptJson()
                ->retry(3, 500)
                ->timeout(60)
                ->get($url);

            if (! $response->successful()) {
                $this->command?->warn("Gagal mengambil data dari {$url}");

                return null;
            }

            $payload = $response->json();

            return is_array($payload) ? $payload : null;
        } catch (\Throwable $exception) {
            $this->command?->warn("Error saat mengambil {$url}: ".$exception->getMessage());

            return null;
        }
    }

    private function normalizeName(mixed $value): string
    {
        return trim((string) $value);
    }

    private function resolveCityType(string $name): string
    {
        return str_starts_with(mb_strtolower($name), 'kota ')
            ? 'city'
            : 'regency';
    }
}
