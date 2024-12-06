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
        $title = rtrim(fake()->sentence(5, false), '.');

        return [
            'brand' => mb_ucfirst(fake()->word()),
            'sku' => $sku,
            'title' => $title,
            'url' => 'https://example.com/product/'.$sku,
            'image_url' => 'https://via.assets.so/shoe.png?id=1'.rand(1, 9999),
        ];
    }
}
