<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'cat'.fake()->randomNumber(6, true);

        return [
            'code' => $code,
            'title' => ucwords(fake()->words(2, true)),
            'url' => 'https://example.com/cat/'.$code,
        ];
    }
}
