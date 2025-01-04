<?php

namespace App\Crawlers\Costco;

use App\Crawlers\JsonBaseCrawler;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Url;

abstract class CostcoMxBaseCrawler extends JsonBaseCrawler
{
    protected static ?string $storeCode = 'costco-mx';

    protected Store $store;

    protected function setup(): void
    {
        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => 'costco',
        ], [
            'name' => 'Costco',
            'external_url' => 'https://www.costco.com.mx',
        ]);
    }

    protected function saveProduct(array $data, string $source): void
    {
        $crawl = 'https://www.costco.com.mx/rest/v2/mexico/products/%s/?fields=FULL&lang=es_MX&curr=MXN';
        $href = sprintf($crawl, $data['sku']);
        $url = Url::resolve($href, 30);

        if ($source == 'category') {
            $url?->delay();
        }

        $product = Product::whereStoreId($this->store->id)->whereSku($data['sku'])->first();

        if (! $product) {
            $product = Product::create([
                'store_id' => $this->store->id,
                'sku' => $data['sku'],
                'brand' => null,
                'title' => strip_tags($data['title']),
                'url_id' => $url?->id,
                'external_url' => $data['external_url'],
                'image_url' => $data['image_url'],
            ]);

            $product->categories()->syncWithoutDetaching($data['categories']);
        }

        Product::withoutSyncingToSearch(function () use ($product, $data, $source) {
            $price = (float) str_replace(['$', ','], '', $data['price']);

            $product->prices()->create([
                'price' => $price,
                'source' => 'costco-'.$source,
            ]);
        });
    }

    protected function getImageUrl(object $product): ?string
    {
        if (isset($product->images)) {
            foreach ($product->images as $img) {
                if ($img?->format == 'results') {
                    return 'https://www.costco.com.mx'.$img->url;
                }
            }
        }

        return 'https://placehold.co/400?text=Not%20Found';
    }

    protected function getCategory(object $json): ?Category
    {
        $categories = [];

        if (isset($json->category->url)) {
            $categories = [$json->category];

            if (isset($json->category->supercategories)) {
                $categories = [$json->category, ...$json->category->supercategories];
            }
        } elseif (isset($json->supercategories)) {
            $categories = $json->supercategories;
        } else {
            return null;
        }

        $parentId = null;
        $lastCategory = null;

        $categories = array_reverse($categories);

        foreach ($categories as $cat) {
            $category = Category::firstOrCreate([
                'store_id' => $this->store->id,
                'code' => $cat->code,
            ], [
                'title' => $cat->name,
                'external_url' => 'https://www.costco.com.mx'.$cat->url,
                'parent_id' => $parentId,
            ]);

            $parentId = $category->id;
            $lastCategory = $category;
        }

        return $lastCategory;
    }
}
