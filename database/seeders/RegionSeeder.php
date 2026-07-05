<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('regions')->delete();
        $country = DB::table('countries')->whereIso('SA')->first();
        $json_file = file_get_contents(__DIR__.'/../initial_data/regions.json');

        $json = json_decode($json_file, true);

        $regions = [];

        foreach ($json as $data) {
            $regions[] = [
                'id' => $data['region_id'],
                'name' => json_encode($data['name']),
                'country_id' => $country->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($regions, 1000);
        foreach ($chunks as $chunk) {
            DB::table('regions')->insert($chunk);
        }
    }
}
