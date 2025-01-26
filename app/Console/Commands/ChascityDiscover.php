<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ChascityDiscover extends Command
{
    protected $signature = 'chascity:discover {--limit=10}';

    protected $description = 'Schedule products without Chascity prices';

    public function handle()
    {
        // crawling now!
        $this->schedule('sears');

        // just for newly discovered products
        $this->schedule('liverpool');
        $this->schedule('costco');
        $this->schedule('palacio');
        $this->schedule('suburbia');
    }

    private function schedule(string $storeSlug): void
    {
        $store = Store::whereCountry('mx')->whereSlug($storeSlug)->first();

        if (! $store) {
            return;
        }

        $nextId = (int) cache('chascity.next-id-'.$storeSlug, 0);

        // keep the cache alive
        cache(['chascity.next-id-'.$storeSlug => $nextId], now()->addHours(24));

        if ($nextId == 0) {
            $nextId = Product::whereStoreId($store->id)->first()->id;
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

        $chascitySlug = match ($storeSlug) {
            'palacio' => 'palaciohierro',
            default => $storeSlug,
        };

        foreach ($products as $product) {
            $href = sprintf(
                'https://preciominimo.chascity.com/verificaprecio/%s?sku=%s',
                $chascitySlug,
                $product->sku,
            );

            $url = Url::resolve($href, 30);
            $alreadyResolved = $url && $url->crawled_at ? 'skip' : 'follow';

            $this->line("[Product {$product->id}] [$alreadyResolved] {$href}");

            cache(['chascity.next-id-'.$storeSlug => $product->id], now()->addHours(24));
        }
    }
}
