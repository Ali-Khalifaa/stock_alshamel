<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        foreach (range(0, 10) as $n) {
           Module::create([
               'name' => 'Module ' . $n,
               'name_e' => 'Module ' . $n,
               'parent_id' => 0,
               'is_active' => 'active',
           ]);
        }

        Module::factory(10)->create();

    }
}
