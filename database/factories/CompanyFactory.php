<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "partner_id"    => $this->faker->numberBetween( 1 , count(Partner::all()) ),
            "name"       => $this->faker->name(),
            "name_e"     => $this->faker->name(),
            "url"        => $this->faker->name(),
            "address"    => $this->faker->name(),
            "phone"      => $this->faker->phoneNumber(),
            "cr"         => $this->faker->randomNumber(),
            "tax_id"     => $this->faker->randomNumber(),
            "vat_no"     => $this->faker->randomNumber(),
            "email"      => $this->faker->email(),
            "website"    => $this->faker->name()
        ];
    }
}
