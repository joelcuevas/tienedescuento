<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Console\Command;

class FixPalacioCategories extends Command
{
    protected $signature = 'fix:palacio';

    protected $description = 'Command description';

    public function handle()
    {
        $products = Product::whereDoesntHave('categories')->limit(1000)->get();
        $store = Store::whereSlug('palacio')->first();

        $category = Category::firstOrCreate([
            'store_id' => $store->id,
            'code' => 'palacio',
        ], [
            'title' => 'Palacio',
            'external_url' => 'https://www.elpalaciodehierro.com',
            'parent_id' => null,
        ]);

        foreach ($products as $product) {
            dispatch(function () use ($product, $category) {
                $product->categories()->syncWithoutDetaching($category);
            });
        }
    }
}
