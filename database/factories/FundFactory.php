<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fund>
 */
class FundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'class' => fake()->randomElement(['A', 'B', 'C', null]),
            'data' => null,
            'user_id' => User::factory(),
        ];
    }

    public function withData(array $data = []): static
    {
        return $this->state(['data' => $data]);
    }
}
2