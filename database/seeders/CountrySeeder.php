<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $data = [];

        $path = base_path('countries.json');

        if (File::exists($path)) {
            $this->command?->info('Menggunakan file lokal: countries.json');
            try {
                $countries = json_decode(File::get($path), true);

                if (empty($countries)) {
                    $this->command?->warn('File countries.json kosong.');
                }

                foreach ($countries as $item) {
                    $iso = Str::lower((string) data_get($item, 'cca2', ''));
                    $officialName = trim((string) data_get($item, 'name.official', ''));
                    $commonName = trim((string) data_get($item, 'name.common', ''));

                    if ($iso === '' || $officialName === '' || $commonName === '') {
                        continue;
                    }

                    $root = (string) data_get($item, 'idd.root', '');
                    $suffixes = data_get($item, 'idd.suffixes', []);
                    $suffix = is_array($suffixes) ? (string) ($suffixes[0] ?? '') : '';
                    $normalizedPhoneCode = str_replace(['+', ' '], '', $root.$suffix);
                    $numcode = data_get($item, 'ccn3');

                    $data[] = [
                        'iso' => $iso,
                        'name' => $officialName,
                        'nice_name' => $commonName,
                        'iso3' => data_get($item, 'cca3'),
                        'numcode' => $numcode !== null && $numcode !== '' ? (int) $numcode : null,
                        'phone_code' => $normalizedPhoneCode !== '' ? (int) $normalizedPhoneCode : 0,
                        'status' => true,
                    ];
                }
            } catch (\Throwable $exception) {
                $this->command?->warn('Gagal memproses file lokal: '.$exception->getMessage());
            }
        }

        if (empty($data)) {
            $this->command?->warn('Menggunakan data fallback untuk Indonesia agar seeder lain tetap berjalan.');
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

        foreach (array_chunk($data, 50) as $chunk) {
            Country::query()->upsert($chunk, ['iso'], ['name', 'nice_name', 'iso3', 'numcode', 'phone_code', 'status']);
        }

        $this->command?->info('Berhasil mengimpor '.count($data).' data negara.');
    }
}
