<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name,
            "name_e" => $this->faker->name,
            "parent_id" => \App\Models\Module::all()->random()->id,
            "is_active" => $this->faker->randomElement(["active", "inactive"]),
        ];
    }
}
