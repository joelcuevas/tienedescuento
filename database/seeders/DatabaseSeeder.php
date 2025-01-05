<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Taxonomy;
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
        Artisan::call('url:crawl "https://www.liverpool.com.mx/tienda/pdp/samsung-galaxy-s24-fe-dynamic-amoled-2x-6.7-pulgadas-desbloqueado/1164289479?skuid=1164289482"');
        Artisan::call('url:crawl "https://www.costco.com.mx/rest/v2/mexico/products/685767/?fields=FULL&lang=es_MX&curr=MXN"');
        Artisan::call('url:crawl "https://www.liverpool.com.mx/tienda/celulares/cat5150024"');
        Artisan::call('url:crawl "https://www.costco.com.mx/rest/v2/mexico/products/search?category=cos_1.3.1&currentPage=0&pageSize=25&lang=es_MX&curr=MXN&fields=FULL"');

        $store = Store::factory()->create();

        Product::factory(60)
            ->for($store)
            ->hasPrices(10)
            ->has(Category::factory(1)->state(fn () => ['store_id' => $store->id]))
            ->create();

        Artisan::call('taxonomy:build');
    }
}
