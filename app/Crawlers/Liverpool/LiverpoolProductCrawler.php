<?php

namespace App\Crawlers\Liverpool;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolProductCrawler extends LiverpoolBaseCrawler
{
    protected static string $pattern = '#^https://www\.liverpool\.com\.mx/tienda/pdp/(?:.+/)?(\d+)(?:\?.*)?$#';

    protected int $cooldown = 3;

    public function resolveProduct(): ?Product
    {
        if (preg_match(static::$pattern, $this->url->href, $matches)) {
            $sku = $matches[1];
            $store = Store::whereCountry('mx')->whereSlug('liverpool')->first();

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

    protected function parse(Crawler $dom): int
    {
        $data = $dom->filter('#__NEXT_DATA__');

        // check if there is processable data on the page
        if ($data->count() == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        $results = json_decode($data->text());

        if (! isset($results->query->data->mainContent)) {
            return Response::HTTP_NO_CONTENT;
        }

        // yes, there is! save the product
        $mainContent = $results->query->data->mainContent;

        if (isset($mainContent->records)) {
            foreach ($mainContent->records as $record) {
                $this->saveProduct($record, 'product');
            }
        }

        // aaand... done!
        return Response::HTTP_OK;
    }
}
