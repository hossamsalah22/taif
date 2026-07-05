<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('districts')->delete();

        $json_file = file_get_contents(__DIR__.'/../initial_data/districts.json');
        $json = json_decode($json_file, true);

        $districts = [];

        foreach ($json as $data) {
            $districts[] = [
                'id' => $data['id'],
                'city_id' => $data['city_id'],
                'name' => json_encode(['ar' => $data['name_ar'], 'en' => $data['name_en']]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($districts, 1000);
        foreach ($chunks as $chunk) {
            DB::table('districts')->insert($chunk);
        }
    }
}
