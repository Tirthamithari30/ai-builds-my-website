<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'category' => fake()->randomElement(['restaurant', 'cafe', 'salon', 'consultant', 'retail', 'fitness']),
            'description' => fake()->paragraph(),
            'email' => fake()->companyEmail(),
            'phone' => '+91 ' . fake()->numerify('##########'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'India',
            'operating_hours' => ['raw' => 'Mon-Sat: 9:00 AM - 8:00 PM'],
            'products' => ['Premium Offering 1', 'Specialty Item 2'],
            'services' => ['Consultation', 'Standard Service', 'Express Delivery'],
            'status' => 'draft',
        ];
    }
}
