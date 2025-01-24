<?php

namespace App\Crawlers\Chascity;

use App\Crawlers\WebBaseCrawler;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class ChascityProductCrawler extends WebBaseCrawler
{
    protected static ?string $pattern = '#^https://preciominimo\.chascity\.com/verificaprecio/([^?]+)\?sku=([^&]+)$#';

    public function resolveProduct(): ?Product
    {
        preg_match(static::$pattern, $this->url->href, $matches);

        $storeSlug = match ($matches[1]) {
            'palaciohierro' => 'palacio',
            default => $matches[1],
        };

        $store = Store::whereCountry('mx')->whereSlug($storeSlug)->first();

        if ($store) {
            return Product::whereStoreId($store->id)->whereSku($matches[2])->first();
        }

        return null;
    }

    protected function recentlyCrawled(): bool
    {
        $product = $this->resolveProduct();

        if ($product) {
            // skip crawling if the product was already crawled in chascity
            $crawled = $product->prices()->where('source', 'chascity')->count() > 0;

            if ($crawled) {
                $this->hit(Response::HTTP_IM_USED);

                return true;
            }
        }

        return false;
    }

    protected function parse(mixed $dom): int
    {
        $data = $dom->filter('.table-striped > tbody');

        // the product wasn't found on the page, probably an error
        if ($data->count() == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        // parse the prices table
        $prices = $data->filter('tr')->each(function (Crawler $tr) {
            $tds = $tr->filter('td');
            $date = $tds->eq(0)->text();
            $priceRaw = $tds->eq(1)->text();
            $price = (float) str_replace(['$', ','], '', $priceRaw);

            return [$price, $date];
        });

        // store the prices for the product
        $product = $this->resolveProduct();

        Product::withoutSyncingToSearch(function () use ($product, $prices) {
            foreach ($prices as $price) {
                $product->prices()->create([
                    'price' => $price[0],
                    'source' => 'chascity',
                    'priced_at' => new Carbon($price[1]),
                ]);
            }
        });

        $this->crawledProducts++;

        // good; do not re-crawl anytime soon
        return Response::HTTP_IM_USED;
    }
}
