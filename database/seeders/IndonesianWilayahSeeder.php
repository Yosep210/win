<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class IndonesianWilayahSeeder extends Seeder
{
    protected string $baseUrl = 'https://raw.githubusercontent.com/mfitrahrmd/wilayah-indonesia-json/master/';

    public function run(): void
    {
        // 1. Pastikan Indonesia ada di tabel countries dan dapatkan ID-nya
        $indonesia = Country::where('iso', 'id')->first();
        if (!$indonesia) {
            $this->command->error("Data negara Indonesia tidak ditemukan. Jalankan CountrySeeder terlebih dahulu.");
            return;
        }

        // 2. Import Provinces
        $this->command->info("Fetching Provinces...");
        $response = Http::get($this->baseUrl . 'provinces.json');
        if ($response->successful()) {
            $provinces = $response->json();
            $dataProvince = array_map(fn($item) => [
                'id'         => $item['id'],
                'country_id' => $indonesia->id,
                'name'       => $item['name'],
                'code'       => $item['code'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $provinces);

            DB::table('provinces')->upsert($dataProvince, ['id'], ['name', 'updated_at']);
            $this->command->info("Provinces imported.");
        }

        // 3. Import Cities (Regencies)
        $this->command->info("Fetching Cities...");
        $response = Http::get($this->baseUrl . 'regencies.json');
        if ($response->successful()) {
            $cities = $response->json();
            $dataCity = array_map(fn($item) => [
                'id'          => $item['id'],
                'province_id' => $item['province_id'],
                'name'        => $item['name'],
                'type'        => $item['type'] ?? null,
                'code'        => $item['code'] ?? null,
                'postal_code' => $item['postal_code'] ?? null,
                'external_id' => $item['external_id'] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ], $cities);

            DB::table('cities')->upsert($dataCity, ['id'], ['name', 'province_id', 'updated_at']);
            $this->command->info("Cities imported.");
        }

        // 4. Import Districts (Kecamatan)
        $this->command->info("Fetching Districts...");
        $response = Http::get($this->baseUrl . 'districts.json');
        if ($response->successful()) {
            $districts = $response->json();
            $dataDistrict = array_map(fn($item) => [
                'id'          => $item['id'],
                'city_id'     => $item['regency_id'] ?? $item['city_id'], // Menyesuaikan source JSON
                'name'        => $item['name'],
                'postal_code' => $item['postal_code'] ?? null,
                'external_id' => $item['external_id'] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ], $districts);

            foreach (array_chunk($dataDistrict, 1000) as $chunk) {
                DB::table('districts')->upsert($chunk, ['id'], ['name', 'city_id', 'updated_at']);
            }
            $this->command->info("Districts imported.");
        }

        // 5. Import Villages (Kelurahan/Desa)
        $this->command->info("Fetching Villages (This might take a while)...");
        $response = Http::get($this->baseUrl . 'villages.json');
        if ($response->successful()) {
            // Karena data Village sangat besar, kita proses dengan hati-hati
            $villages = $response->json();

            $batch = [];
            $count = 0;

            foreach ($villages as $item) {
                $batch[] = [
                    'id'          => $item['id'],
                    'district_id' => $item['district_id'],
                    'name'        => $item['name'],
                    'postal_code' => $item['postal_code'] ?? null,
                    'external_id' => $item['external_id'] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $count++;

                // Upsert setiap 2000 data agar tidak memberatkan database dan memori
                if (count($batch) >= 2000) {
                    DB::table('villages')->upsert($batch, ['id'], ['name', 'district_id', 'updated_at']);
                    $batch = [];
                }
            }

            // Masukkan sisa data yang belum ter-upsert
            if (!empty($batch)) {
                DB::table('villages')->upsert($batch, ['id'], ['name', 'district_id', 'updated_at']);
            }

            $this->command->info("Total {$count} Villages imported successfully.");
        }
    }
}
