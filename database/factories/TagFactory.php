<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Tag>
 */
final class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
