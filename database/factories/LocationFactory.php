<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Tangerang'];
        $city = $this->faker->randomElement($cities);

        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Office', 'Branch', 'Center']),
            'code' => strtoupper($this->faker->lexify('???')),
            'address' => $this->faker->streetAddress(),
            'city' => $city,
            'state' => $city,
            'country' => 'Indonesia',
            'postal_code' => $this->faker->postcode(),
            'phone' => '+62 ' . $this->faker->numberBetween(10, 99) . ' ' . $this->faker->numberBetween(1000000, 9999999),
            'email' => $this->faker->companyEmail(),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}
