<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('product:sample --stores');

        $store = Store::factory()->create();

        Product::factory(60)
            ->for($store)
            ->hasPrices(10)
            ->has(Category::factory(1)->state(fn () => ['store_id' => $store->id]))
            ->create();

        Artisan::call('taxonomy:build');
    }
}
