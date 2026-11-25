<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Website>
 */
class WebsiteFactory extends Factory
{
    protected $model = Website::class;

    public function definition(): array
    {
        $title = fake()->company() . ' Website';

        return [
            'business_id' => Business::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'font_family' => 'Inter, system-ui, sans-serif',
            'meta_description' => fake()->sentence(),
            'is_published' => false,
            'current_version' => 1,
            'settings' => [],
        ];
    }
}
