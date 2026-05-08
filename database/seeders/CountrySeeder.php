<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $data = [];

        $path = base_path('countries.json');

        if (File::exists($path)) {
            $this->command->info('Menggunakan file lokal: countries.json');
            try {
                $countries = json_decode(File::get($path), true);

                if (empty($countries)) {
                    $this->command->warn('File countries.json kosong.');
                }

                foreach ($countries as $item) {
                    $root = $item['idd']['root'] ?? '';
                    $suffix = is_array($item['idd']['suffixes'] ?? null) ? ($item['idd']['suffixes'][0] ?? '') : '';
                    $phoneCode = (int) str_replace(['+', ' '], '', $root . $suffix);

                    $data[] = [
                        'iso' => Str::lower($item['cca2'] ?? ''),
                        'name' => $item['name']['official'] ?? '',
                        'nice_name' => $item['name']['common'] ?? '',
                        'iso3' => $item['cca3'] ?? null,
                        'numcode' => ($item['ccn3'] !== '') ? (int) $item['ccn3'] : null,
                        'phone_code' => $phoneCode,
                        'status' => true,
                    ];
                }
            } catch (\Exception $e) {
                $this->command->warn('Gagal memproses file lokal: ' . $e->getMessage());
            }
        }

        // Fallback: Pastikan minimal ada data Indonesia jika API gagal
        if (empty($data)) {
            $this->command->warn('Menggunakan data fallback untuk Indonesia agar seeder lain tetap berjalan.');
            $data[] = [
                'iso' => 'id',
                'name' => 'Republic of Indonesia',
                'nice_name' => 'Indonesia',
                'iso3' => 'IDN',
                'numcode' => 360,
                'phone_code' => 62,
                'status' => true,
            ];
        }

        // Menggunakan upsert agar jika dijalankan ulang tidak terjadi duplikat berdasarkan kolom 'iso'.
        foreach (array_chunk($data, 50) as $chunk) {
            Country::upsert($chunk, ['iso'], ['name', 'nice_name', 'iso3', 'numcode', 'phone_code', 'status']);
        }

        $this->command->info('Berhasil mengimpor ' . count($data) . ' data negara.');
    }
}
