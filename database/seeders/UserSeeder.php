<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Parent 01',
            'country_code' => 'SA',
            'phone' => '+966500000001',
            'email' => 'parent@example.com',
            'is_verified' => true,
            'status' => UserStatusEnum::ACCEPTED,
        ]);
    }
}
