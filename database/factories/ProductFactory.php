<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $sku = fake()->randomNumber(8, true);

        return [
            'brand' => mb_ucfirst(fake()->word()),
            'sku' => $sku,
            'title' => fake()->sentence(),
            'url' => 'https://example.com/product/'.$sku,
            'image_url' => 'https://placehold.co/600?text='.$sku,
        ];
    }
}
