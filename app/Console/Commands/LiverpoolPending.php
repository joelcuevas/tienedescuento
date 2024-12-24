<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class LiverpoolPending extends Command
{
    protected $signature = 'liverpool:pending {--limit=25}';

    protected $description = 'Schedule for crawling products missed in Liverpool categories';

    public function handle()
    {
        $store = Store::where('country', 'mx')->whereSlug('liverpool')->first();
        $nextId = (int) cache('liverpool.next-id', 0);

        $products = Product::query()
            ->where('priced_at', '<', now()->subHours(18))
            ->whereStoreId($store->id)
            ->where('id', '>', $nextId)
            ->where('id', '<', $nextId + 100)
            ->orderBy('id')
            ->limit($this->option('limit'))
            ->get();

        foreach ($products as $product) {
            Url::resolve($product->url);

            $this->line("[Product {$product->id}] {$product->url}");

            cache(['liverpool.next-id' => $product->id]);
        }
    }
}
