<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SponsoredAd>
 */
class SponsoredAdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = ['#fc8019', '#4A9DFF', '#FF5722', '#4CAF50', '#2196F3', '#FFC107', '#9C27B0', '#E91E63'];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(2),
            'image_url' => $this->faker->imageUrl(800, 400, 'business'),
            'target_url' => $this->faker->url(),
            'sponsor_name' => $this->faker->company(),
            'sponsor_logo' => $this->faker->imageUrl(100, 100, 'business', true),
            'cta_text' => $this->faker->randomElement(['Learn More', 'Shop Now', 'Get Started', 'View Details', 'Try Free']),
            'background_color' => $this->faker->randomElement($colors),
            'text_color' => '#FFFFFF',
            'is_active' => true,
            'priority' => $this->faker->numberBetween(0, 10),
            'starts_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'ends_at' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
            'impressions_count' => $this->faker->numberBetween(100, 10000),
            'clicks_count' => function (array $attributes) {
                // Ensure clicks are less than impressions
                return $this->faker->numberBetween(10, min(1000, $attributes['impressions_count']));
            },
        ];
    }

    /**
     * Indicate that the ad is active.
     *
     * @return Factory
     */
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(30),
            ];
        });
    }

    /**
     * Indicate that the ad is inactive.
     *
     * @return Factory
     */
    public function inactive(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    /**
     * Indicate that the ad is expired.
     *
     * @return Factory
     */
    public function expired(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'starts_at' => now()->subDays(60),
                'ends_at' => now()->subDays(1),
            ];
        });
    }

    /**
     * Indicate that the ad is scheduled for the future.
     *
     * @return Factory
     */
    public function scheduled(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'starts_at' => now()->addDays(1),
                'ends_at' => now()->addDays(30),
            ];
        });
    }

    /**
     * Indicate that the ad has high priority.
     *
     * @return Factory
     */
    public function highPriority(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => $this->faker->numberBetween(8, 10),
            ];
        });
    }
}
