<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'name_e' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->name(),
            'mobile_no' => $this->faker->phoneNumber()
        ];
    }
}
