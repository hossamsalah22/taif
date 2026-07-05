<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cities')->delete();

        $json_file = file_get_contents(__DIR__.'/../initial_data/cities.json');
        $json = json_decode($json_file, true);

        $cities = [];

        foreach ($json as $data) {
            $cities[] = [
                'id' => $data['city_id'],
                'region_id' => $data['region_id'],
                'name' => json_encode(['ar' => $data['name_ar'], 'en' => $data['name_en']]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($cities, 1000);
        foreach ($chunks as $chunk) {
            DB::table('cities')->insert($chunk);
        }
    }
}
