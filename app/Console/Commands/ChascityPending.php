<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ChascityPending extends Command
{
    protected $signature = 'chascity:pending {--limit=10}';

    protected $description = 'Schedule for crawling products without Chascity prices';

    public function handle()
    {
        $this->schedule('liverpool');
        $this->schedule('costco');
    }

    private function schedule(string $storeSlug): void
    {
        $store = Store::whereCountry('mx')->whereSlug($storeSlug)->first();

        if ($storeSlug == 'liverpool') {
            $nextId = (int) cache('chascity.next-id1', 0);
        } else {
            $nextId = (int) cache('chascity.next-id-'.$storeSlug, 0);
        }

        $products = Product::query()
            ->whereStoreId($store->id)
            ->whereDoesntHave('prices', function (Builder $query) {
                $query->whereSource('chascity');
            })
            ->where('id', '>', $nextId)
            ->where('id', '<', $nextId + 100)
            ->orderBy('id')
            ->limit($this->option('limit'))
            ->get();

        foreach ($products as $product) {
            $href = sprintf(
                'https://preciominimo.chascity.com/verificaprecio/%s?sku=%s',
                $storeSlug,
                $product->sku,
            );

            $url = Url::resolve($href);
            $alreadyResolved = $url->crawled_at ? 'skip' : 'follow';

            $this->line("[Product {$product->id}] [$alreadyResolved] {$href}");

            if ($storeSlug == 'liverpool') {
                cache(['chascity.next-id1' => $product->id]);
            }

            cache(['chascity.next-id-'.$storeSlug => $product->id]);
        }
    }
}
