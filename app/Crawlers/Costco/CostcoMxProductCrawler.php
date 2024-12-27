<?php

namespace App\Crawlers\Costco;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;

class CostcoMxProductCrawler extends CostcoMxBaseCrawler
{
    protected static string $pattern = '#^https://www\.costco\.com\.mx/rest/v2/mexico/products/(?!search\b)([\w-]+)(?:[/?].*)?$#';

    protected int $cooldown = 2;

    public function resolveProduct(): ?Product
    {
        if (preg_match(static::$pattern, $this->url->href, $matches)) {
            $sku = $matches[1];
            $store = Store::whereCountry('mx')->whereSlug('costco')->first();

            if ($store) {
                return $store->products()->whereSku($sku)->first();
            }
        }

        return null;
    }

    protected function recentlyCrawled(): bool
    {
        $product = $this->resolveProduct();

        if ($product) {
            // skip crawling if product was already priced today
            return $product->priced_at && $product->priced_at >= now()->startOfDay();
        }

        return false;
    }

    protected function parse(mixed $json): int
    {
        // check if there is processable data on the page
        if (! isset($json->code)) {
            return Response::HTTP_NO_CONTENT;
        }

        if ($json?->stock?->stockLevelStatus == 'inStock') {
            if (isset($json->price->value)) {
                $data['sku'] = $json->code;
                $data['title'] = $json->name;
                $data['external_url'] = 'https://www.costco.com.mx'.$json->url;
                $data['image_url'] = $this->getImageUrl($json);
                $data['price'] = $json->price->value;

                $category = $this->getCategory($json);
                $data['categories'] = [$category];

                $this->saveProduct($data, 'product');
            }
        }

        // we are done!
        return Response::HTTP_OK;
    }
}
