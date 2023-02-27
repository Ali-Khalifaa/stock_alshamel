<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Partner::create([
            'name' => 'محمد',
            'name_e' => 'Mohamed',
            'is_active' => 'active',
            'email' => 'test@test.com',
            'password' => bcrypt('123'),
            'mobile_no' => '01000000000',
        ]);
    }
}
