<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'symbol' => $this->faker->randomElement(['BTC', 'ETH']),
            'amount' => $this->faker->randomFloat(8, 0, 100),
            'locked_amount' => 0,
        ];
    }
}
