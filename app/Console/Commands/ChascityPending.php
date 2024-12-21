<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ChascityPending extends Command
{
    protected $signature = 'chascity:pending {--limit=25}';

    protected $description = 'Schedule for crawling products without Chascity prices';

    public function handle()
    {
        $stores = Store::whereIn('slug', ['liverpool'])->get();
        $nextId = (int) cache('chascity.next-id1', 0);

        $products = Product::query()
            ->whereIn('store_id', $stores->pluck('id')->all())
            ->whereDoesntHave('prices', function (Builder $query) {
                $query->whereSource('chascity');
            })
            ->where('id', '>', $nextId)
            ->where('id', '<', $nextId + 100)
            ->orderBy('id')
            ->with('store')
            ->limit($this->option('limit'))
            ->get();

        foreach ($products as $product) {
            $href = sprintf(
                'https://preciominimo.chascity.com/verificaprecio/%s?sku=%s',
                $product->store->slug,
                $product->sku,
            );

            $url = Url::resolve($href);
            $alreadyResolved = $url->crawled_at ? 'skip' : 'follow';

            $this->line("[Product {$product->id}] [$alreadyResolved] {$href}");

            cache(['chascity.next-id1' => $product->id]);
        }
    }
}
