<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Url;
use Illuminate\Console\Command;

class FixProductUrls extends Command
{
    protected $signature = 'fix:urls';

    protected $description = 'Fixes the missing URLs in products table';

    public function handle()
    {
        $nextId = (int) cache('fix-url.next-id', 0);

        $products = Product::whereNull('url_id')
            ->where('id', '>', $nextId)
            ->where('id', '<', $nextId + 500)
            ->take(2000)
            ->orderBy('id')
            ->get();

        foreach ($products as $product) {
            dispatch(function () use ($product) {
                $hash = sha1($product->external_url);
                $url = Url::whereHash($hash)->first();

                if ($url) {
                    $product->url_id = $url->id;
                    $product->save();
                }
            });

            $this->line("[Product {$product->id}] {$product->external_url}");
            cache(['fix-url.next-id' => $product->id], now()->addHours(24));
        }
    }
}
