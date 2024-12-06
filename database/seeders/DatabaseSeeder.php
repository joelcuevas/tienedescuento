<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $store = Store::factory()->create();

        Product::factory(10)
            ->for($store)
            ->hasPrices(10)
            ->has(Category::factory(1)->state(fn () => ['store_id' => $store->id]))
            ->create();
    }
}
