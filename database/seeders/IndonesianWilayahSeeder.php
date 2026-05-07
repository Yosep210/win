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
        // Meningkatkan limit memori dan waktu eksekusi karena data wilayah sangat besar
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $indonesia = Country::where('iso', 'id')->first();
        if (! $indonesia) {
            $this->command->error('Data negara Indonesia tidak ditemukan.');

            return;
        }

        // Reset tabel agar auto-increment mulai dari 1 lagi
        Schema::disableForeignKeyConstraints();
        DB::table('villages')->truncate();
        DB::table('districts')->truncate();
        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        Schema::enableForeignKeyConstraints();

        $sources = [
            ['name' => 'emsifa/api-wilayah', 'base' => 'https://emsifa.github.io/api-wilayah-indonesia/api'],
        ];

        foreach ($sources as $source) {
            $this->command->info("Trying {$source['name']}...");
            if ($this->tryImportFromSource($source['base'], $indonesia)) {
                return;
            }
        }

        $this->command->error('❌ All online sources failed. Seeding aborted.');
    }

    private function tryImportFromSource(string $baseUrl, Country $indonesia): bool
    {
        try {
            $this->command->line("  Fetching provinces from: $baseUrl/provinces.json");
            $response = Http::withoutVerifying()->timeout(30)->get("$baseUrl/provinces.json");

            if (! $response->successful()) {
                $this->command->warn("  ✗ HTTP {$response->status()}");

                return false;
            }

            $provinces = $response->json();
            if (empty($provinces)) {
                $this->command->warn('  ✗ Empty provinces response');

                return false;
            }

            $this->command->line('  Got ' . count($provinces) . ' provinces');

            // Cek apakah sumber data menggunakan metode iteratif (github.io) atau file tunggal
            $isIterative = str_contains($baseUrl, 'github.io');

            if ($isIterative) {
                foreach ($provinces as $province) {
                    $this->command->line("    Processing: " . Str::title($province['name']));

                    // Simpan Provinsi dan ambil ID auto-increment-nya
                    $localProvinceId = DB::table('provinces')->insertGetId([
                        'country_id' => $indonesia->id,
                        'name' => Str::title($province['name']),
                        // 'code' => $province['id'], // Simpan ID asli API di kolom code
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $resCities = Http::withoutVerifying()->timeout(30)->get("$baseUrl/regencies/{$province['id']}.json");
                    if ($resCities->successful()) {
                        $cities = $resCities->json();
                        foreach ($cities as $city) {
                            $localCityId = DB::table('cities')->insertGetId([
                                'province_id' => $localProvinceId,
                                'name' => Str::title($city['name']),
                                // 'external_id' => $city['id'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $resDist = Http::withoutVerifying()->timeout(30)->get("$baseUrl/districts/{$city['id']}.json");
                            if ($resDist->successful()) {
                                $districts = $resDist->json();
                                foreach ($districts as $district) {
                                    $localDistrictId = DB::table('districts')->insertGetId([
                                        'city_id' => $localCityId,
                                        'name' => Str::title($district['name']),
                                        // 'external_id' => $district['id'],
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);

                                    $resVil = Http::withoutVerifying()->timeout(30)->get("$baseUrl/villages/{$district['id']}.json");
                                    if ($resVil->successful()) {
                                        $villages = $resVil->json();
                                        $villageData = array_map(fn($v) => [
                                            'district_id' => $localDistrictId,
                                            'name' => Str::title($v['name']),
                                            // 'external_id' => $v['id'],
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ], $villages);
                                        DB::table('villages')->insert($villageData);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Jika bukan iteratif, kita pakai mapping array (lebih cepat tapi memori besar)
                $this->importProvinces($provinces, $indonesia);

                $resCities = Http::withoutVerifying()->timeout(60)->get("$baseUrl/regencies.json");
                if ($resCities->successful()) {
                    $provMap = DB::table('provinces')->pluck('id', 'code')->toArray();
                    $cityData = array_map(fn($c) => [
                        'province_id' => $provMap[$c['province_id']] ?? null,
                        'name' => Str::title($c['name']),
                        // 'external_id' => $c['id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $resCities->json());
                    DB::table('cities')->insert($cityData);
                }

                $resDist = Http::withoutVerifying()->timeout(60)->get("$baseUrl/districts.json");
                if ($resDist->successful()) {
                    $cityMap = DB::table('cities')->pluck('id', 'external_id')->toArray();
                    foreach (array_chunk($resDist->json(), 500) as $chunk) {
                        $distData = array_map(fn($d) => [
                            'city_id' => $cityMap[$d['regency_id']] ?? null,
                            'name' => Str::title($d['name']),
                            // 'external_id' => $d['id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], $chunk);
                        DB::table('districts')->insert($distData);
                    }
                }

                $resVil = Http::withoutVerifying()->timeout(600)->get("$baseUrl/villages.json");
                if ($resVil->successful()) {
                    $distMap = DB::table('districts')->pluck('id', 'external_id')->toArray();
                    foreach (array_chunk($resVil->json(), 500) as $chunk) {
                        $vilData = array_map(fn($v) => [
                            'district_id' => $distMap[$v['district_id']] ?? null,
                            'name' => Str::title($v['name']),
                            // 'external_id' => $v['id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], $chunk);
                        DB::table('villages')->insert($vilData);
                    }
                }
            }

            $this->command->info('✓ Import successful!');

            return true;
        } catch (\Throwable $e) {
            $this->command->warn('  Error: ' . $e->getMessage());

            return false;
        }
    }

    private function importProvinces(array $provinces, Country $indonesia): void
    {
        $data = array_map(fn($p) => [
            'country_id' => $indonesia->id,
            'name' => Str::title($p['name']),
            // 'code' => $p['id'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $provinces);

        DB::table('provinces')->insert($data);

        $this->command->info('  ✓ Provinces: ' . count($data));
    }
}
