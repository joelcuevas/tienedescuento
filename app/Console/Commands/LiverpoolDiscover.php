<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Console\Command;

class LiverpoolDiscover extends Command
{
    protected $signature = 'liverpool:discover {--limit=50}';

    protected $description = 'Schedule for crawling Liverpool products with missing URLs';

    public function handle()
    {
        $store = Store::where('country', 'mx')->whereSlug('liverpool')->first();

        $products = Product::query()
            ->whereStoreId($store->id)
            ->whereNull('url_id')
            ->limit($this->option('limit'))
            ->get();

        foreach ($products as $product) {
            Url::resolve($product->external_url);
            $this->line("[Product {$product->id}] {$product->external_url}");
        }
    }
}
