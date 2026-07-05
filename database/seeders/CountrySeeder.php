<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('countries')->delete();

        $json_file = file_get_contents(__DIR__.'/../initial_data/countries.json');
        $json = json_decode($json_file, true);

        $countries = [];

        foreach ($json as $data) {
            $countries[] = [
                'name' => json_encode(['ar' => $data['name_ar'], 'en' => $data['name_en']]),
                'iso' => $data['iso'],
                'phonecode' => $data['phonecode'],
                'numcode' => $data['numcode'],
                'iso3' => $data['iso3'],
                'currency_code' => $data['currency_code'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($countries, 1000);
        foreach ($chunks as $chunk) {
            DB::table('countries')->insert($chunk);
        }
    }
}
