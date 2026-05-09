<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class IndonesianWilayahSeeder extends Seeder
{
    private string $baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $indonesia = Country::query()->where('iso', 'id')->first();

        if (! $indonesia) {
            $this->command?->error('Data negara Indonesia tidak ditemukan. Jalankan CountrySeeder terlebih dahulu.');

            return;
        }

        $provincePath = base_path('provinces.json');

        if (! File::exists($provincePath)) {
            $this->command?->error('File provinces.json tidak ditemukan di root project.');

            return;
        }

        $decoded = json_decode(File::get($provincePath), true);

        if (! is_array($decoded) || empty($decoded)) {
            $this->command?->error('File provinces.json kosong atau formatnya tidak valid.');

            return;
        }

        $provinces = collect($decoded)->sortBy('id')->values()->all();

        Schema::disableForeignKeyConstraints();
        Village::truncate();
        Regency::truncate();
        City::truncate();
        Province::truncate();
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () use ($provinces, $indonesia) {
            $this->seedHierarchy($provinces, $indonesia->id);
        });
    }

    private function seedHierarchy(array $provinces, int $countryId): void
    {
        $provinceCount = 0;

        foreach ($provinces as $province) {
            $provinceApiId = trim((string) data_get($province, 'id', ''));
            $provinceName = $this->normalizeName(data_get($province, 'name'));

            if ($provinceApiId === '' || $provinceName === '') {
                continue;
            }

            $localProvince = Province::create([
                'country_id' => $countryId,
                'name' => $provinceName,
            ]);

            $provinceCount++;
            $this->command?->line("Importing: {$provinceName}");

            $cities = collect($this->fetchJson("regencies/{$provinceApiId}.json") ?? [])
                ->sortBy('id')
                ->values()
                ->all();

            foreach ($cities as $city) {
                $cityApiId = trim((string) data_get($city, 'id', ''));
                $cityName = $this->normalizeName(data_get($city, 'name'));

                if ($cityApiId === '' || $cityName === '') {
                    continue;
                }

                $localCity = City::create([
                    'province_id' => $localProvince->id,
                    'name' => $cityName,
                    'type' => $this->resolveCityType($cityName),
                ]);

                $regencies = collect($this->fetchJson("districts/{$cityApiId}.json") ?? [])
                    ->sortBy('id')
                    ->values()
                    ->all();

                foreach ($regencies as $regency) {
                    $regencyApiId = trim((string) data_get($regency, 'id', ''));
                    $regencyName = $this->normalizeName(data_get($regency, 'name'));

                    if ($regencyApiId === '' || $regencyName === '') {
                        continue;
                    }

                    $localRegency = Regency::create([
                        'city_id' => $localCity->id,
                        'name' => $regencyName,
                    ]);

                    $villages = collect($this->fetchJson("villages/{$regencyApiId}.json") ?? [])
                        ->sortBy('id')
                        ->values()
                        ->all();

                    foreach ($villages as $village) {
                        $villageName = $this->normalizeName(data_get($village, 'name'));

                        if ($villageName === '') {
                            continue;
                        }

                        Village::firstOrCreate(
                            [
                                'regency_id' => $localRegency->id,
                                'name' => $villageName,
                            ],
                            [
                                'postal_code' => null,
                            ]
                        );
                    }
                }
            }
        }

        $this->command?->info("Provinces imported: {$provinceCount}");
        $this->command?->info('Indonesian wilayah import selesai.');
        $this->command?->warn('Village postal_code dibiarkan null. Jalankan IndonesianPostalCodeSeeder untuk mengisinya.');
    }

    /**
     * Fetch JSON dari emsifa API.
     * SSL verify dinonaktifkan langsung dari awal agar tidak ada retry
     * dan urutan insert ke database tetap konsisten/berurutan.
     */
    private function fetchJson(string $endpoint): ?array
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        try {
            $response = Http::acceptJson()
                ->withOptions(['verify' => false])
                ->retry(3, 500)
                ->timeout(60)
                ->get($url);

            if (! $response->successful()) {
                $this->command?->warn("Response tidak sukses: {$url}");

                return null;
            }

            $payload = $response->json();

            return is_array($payload) ? $payload : null;
        } catch (\Throwable $e) {
            $this->command?->warn("Gagal mengambil {$url}: {$e->getMessage()}");

            return null;
        }
    }

    private function normalizeName(mixed $value): string
    {
        return str((string) $value)->squish()->value();
    }

    private function resolveCityType(string $name): string
    {
        return str($name)->lower()->startsWith('kota ') ? 'city' : 'regency';
    }
}
