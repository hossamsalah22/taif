<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Seeder;

class KsaGeodataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $riyadh = Region::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh']);
        $makkah = Region::create(['name_ar' => 'مكة المكرمة', 'name_en' => 'Makkah']);
        $eastern = Region::create(['name_ar' => 'الشرقية', 'name_en' => 'Eastern Province']);

        City::insert([
            ['region_id' => $riyadh->id, 'name_ar' => 'الرياض', 'name_en' => 'Riyadh'],
            ['region_id' => $riyadh->id, 'name_ar' => 'الخرج', 'name_en' => 'Al Kharj'],
            ['region_id' => $makkah->id, 'name_ar' => 'مكة المكرمة', 'name_en' => 'Makkah'],
            ['region_id' => $makkah->id, 'name_ar' => 'جدة', 'name_en' => 'Jeddah'],
            ['region_id' => $makkah->id, 'name_ar' => 'الطائف', 'name_en' => 'Taif'],
            ['region_id' => $eastern->id, 'name_ar' => 'الدمام', 'name_en' => 'Dammam'],
            ['region_id' => $eastern->id, 'name_ar' => 'الخبر', 'name_en' => 'Khobar'],
        ]);
    }
}
