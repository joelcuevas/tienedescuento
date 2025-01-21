<?php

namespace App\Crawlers\Palacio;

use App\Crawlers\WebBaseCrawler;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Symfony\Component\DomCrawler\Crawler;

abstract class PalacioBaseCrawler extends WebBaseCrawler
{
    protected static ?string $storeCode = 'palacio-mx';

    protected Store $store;

    protected array $headers = [
        'User-Agent' => 'Pinterest/0.2 (+http://www.pinterest.com/)',
        'Accept' => '*/*',
    ];

    protected function setup(): void
    {
        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => 'palacio',
        ], [
            'name' => 'El Palacio',
            'external_url' => 'https://www.elpalaciodehierro.com',
        ]);
    }

    protected function saveProduct(object $item, array $categories, string $source): void
    {
        $priceDom = $item->filter('meta[itemprop="lowPrice"]');

        // the product is not available anymore
        if ($priceDom->count() == 0) {
            return;
        }

        $price = $priceDom->attr('content');
        $sku = $item->filter('meta[itemprop="sku"]')->attr('content');
        $href = $item->filter('meta[itemprop="url"]')->attr('content');
        $url = Url::resolve($href, 30);

        if ($source == 'category') {
            $url?->delay();
        }

        $product = Product::whereStoreId($this->store->id)->whereSku($sku)->first();

        if (! $product) {
            $brandDom = $item->filter('meta[itemprop="brand"]');
            $brand = $brandDom->count() ? $brandDom->attr('content') : null;
            $name = $item->filter('meta[itemprop="name"]')->attr('content');
            $image = $item->filter('meta[itemprop="image"]')->attr('content');
            $title = $brand.' '.$name;

            $product = Product::create([
                'store_id' => $this->store->id,
                'sku' => $sku,
                'brand' => $brand,
                'title' => $title,
                'url_id' => $url?->id,
                'external_url' => $href,
                'image_url' => $image,
            ]);
        }

        $product->categories()->syncWithoutDetaching($categories);

        Product::withoutSyncingToSearch(function () use ($product, $price, $source) {
            $product->prices()->create([
                'price' => $price,
                'source' => 'palacio-'.$source,
            ]);
        });
    }

    protected function getCategories(Crawler $dom): array
    {
        $categoryLeafs = [];
        $breadcrumbs = $dom->filter('li > a');

        if ($breadcrumbs->count() > 0) {
            $parentId = null;

            // save the categories
            foreach ($breadcrumbs as $node) {
                $breadcrumb = new Crawler($node);
                $title = $breadcrumb->text();
                $path = $breadcrumb->attr('href');
                $code = 'cat-'.substr(sha1($path), 0, 8);
                $externalUrl = 'https://www.elpalaciodehierro.com'.$path;

                $category = Category::firstOrCreate([
                    'store_id' => $this->store->id,
                    'code' => $code,
                ], [
                    'title' => $title,
                    'external_url' => $externalUrl,
                    'parent_id' => $parentId,
                ]);

                $parentId = $category->id;
            }

            // return only the last category
            $categoryLeafs[] = $category;
        } else {
            logger()->channel('cloudwatch')->info('No breadcrumbs found on the page', [
                'url' => $this->url->href,
            ]);

            $categoryLeafs[] = Category::firstOrCreate([
                'store_id' => $this->store->id,
                'code' => 'palacio',
            ], [
                'title' => 'Palacio',
                'external_url' => 'https://www.elpalaciodehierro.com',
                'parent_id' => null,
            ]);
        }

        return $categoryLeafs;
    }
}
