<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Mengambil data negara dari API...');

        $response = Http::get('https://restcountries.com/v3.1/all');

        if ($response->failed()) {
            $this->command->error('Gagal mengambil data dari API negara.');
            return;
        }

        $countries = $response->json();

        $data = [];
        foreach ($countries as $item) {
            // Logika untuk menggabungkan kode telepon (misal: +6 dan 2 menjadi 62)
            $root = $item['idd']['root'] ?? '';
            $suffix = $item['idd']['suffixes'][0] ?? '';
            $phoneCode = (int) str_replace(['+', ' '], '', $root . $suffix);

            $data[] = [
                'iso'        => Str::lower($item['cca2'] ?? ''),
                'name'       => $item['name']['official'] ?? '',
                'nice_name'  => $item['name']['common'] ?? '',
                'iso3'       => $item['cca3'] ?? null,
                'numcode'    => ($item['ccn3'] !== "") ? (int) $item['ccn3'] : null,
                'phone_code' => $phoneCode,
                'status'     => true,
            ];
        }

        // Menggunakan upsert agar jika dijalankan ulang tidak terjadi duplikat berdasarkan kolom 'iso'
        Country::upsert($data, ['iso'], ['name', 'nice_name', 'iso3', 'numcode', 'phone_code', 'status']);

        $this->command->info('Berhasil mengimpor ' . count($data) . ' data negara dari API.');
    }
}
