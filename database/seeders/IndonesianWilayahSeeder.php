<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IndonesianWilayahSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $indonesia = Country::where('iso', 'id')->first();

        if (! $indonesia) {
            $this->command?->error('Data negara Indonesia tidak ditemukan.');

            return;
        }

        $provincePath = base_path('provinces.json');

        if (! is_file($provincePath)) {
            $this->command?->error('File provinces.json tidak ditemukan.');

            return;
        }

        $provinces = json_decode((string) file_get_contents($provincePath), true);

        if (! is_array($provinces) || $provinces === []) {
            $this->command?->error('File provinces.json kosong atau formatnya tidak valid.');

            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('villages')->truncate();
        DB::table('districts')->truncate();
        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        Schema::enableForeignKeyConstraints();

        $baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

        $this->seedHierarchy($provinces, $indonesia->id, $baseUrl);
    }

    private function seedHierarchy(array $provinces, int $countryId, string $baseUrl): void
    {
        $provinceCount = 0;

        foreach ($provinces as $province) {
            $provinceApiId = (string) ($province['id'] ?? '');

            if ($provinceApiId === '') {
                continue;
            }

            $localProvinceId = DB::table('provinces')->insertGetId([
                'country_id' => $countryId,
                'name' => Str::title($province['name'] ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $provinceCount++;

            $this->command?->line('Processing province: '.Str::title($province['name'] ?? ''));

            $cities = $this->fetchJson("{$baseUrl}/regencies/{$provinceApiId}.json");

            if ($cities === null) {
                continue;
            }

            foreach ($cities as $city) {
                $cityApiId = (string) ($city['id'] ?? '');

                $localCityId = DB::table('cities')->insertGetId([
                    'province_id' => $localProvinceId,
                    'name' => Str::title($city['name'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($cityApiId === '') {
                    continue;
                }

                $districts = $this->fetchJson("{$baseUrl}/districts/{$cityApiId}.json");

                if ($districts === null) {
                    continue;
                }

                foreach ($districts as $district) {
                    $districtApiId = (string) ($district['id'] ?? '');

                    $localDistrictId = DB::table('districts')->insertGetId([
                        'city_id' => $localCityId,
                        'name' => Str::title($district['name'] ?? ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($districtApiId === '') {
                        continue;
                    }

                    $villages = $this->fetchJson("{$baseUrl}/villages/{$districtApiId}.json");

                    if ($villages === null || $villages === []) {
                        continue;
                    }

                    $villageRows = array_map(fn (array $village) => [
                        'district_id' => $localDistrictId,
                        'name' => Str::title($village['name'] ?? ''),
                        // EMSIFA tidak menyediakan kode pos pada endpoint village.
                        'postal_code' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $villages);

                    DB::table('villages')->insert($villageRows);
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
            $response = Http::withoutVerifying()->timeout(60)->get($url);

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
}
