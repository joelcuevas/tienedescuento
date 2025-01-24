<?php

namespace App\Crawlers\Sears;

use App\Crawlers\JsonBaseCrawler;
use App\Models\Product;
use App\Models\Store;
use App\Models\Url;

abstract class SearsBaseCrawler extends JsonBaseCrawler
{
    protected static ?string $storeCode = 'sears-mx';

    protected Store $store;

    protected array $headers = [
        'User-Agent' => 'Pinterest/0.2 (+http://www.pinterest.com/)',
        'Accept' => '*/*',
    ];

    protected function setup(): bool
    {
        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => 'sears',
        ], [
            'name' => 'Sears',
            'external_url' => 'https://www.sears.com.mx',
        ]);

        return true;
    }

    protected function saveProduct(array $data, string $source): void
    {
        $url = Url::resolve($data['external_url'], 30);

        if ($source == 'category') {
            $url?->delay();
        }

        $product = Product::whereStoreId($this->store->id)->whereSku($data['sku'])->first();

        if (! $product) {
            $product = Product::create([
                'store_id' => $this->store->id,
                'sku' => $data['sku'],
                'brand' => $data['brand'],
                'title' => strip_tags($data['title']),
                'url_id' => $url?->id,
                'external_url' => $data['external_url'],
                'image_url' => $data['image_url'],
            ]);
        }

        $product->categories()->syncWithoutDetaching($data['categories']);

        Product::withoutSyncingToSearch(function () use ($product, $data, $source) {
            $price = (float) str_replace(['$', ','], '', $data['price']);

            $product->prices()->create([
                'price' => $price,
                'source' => 'sears-'.$source,
            ]);
        });
    }
}
