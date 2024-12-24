<?php

namespace App\Crawlers\Chascity;

use App\Crawlers\BaseCrawler;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class ChascityProductCrawler extends BaseCrawler
{
    protected static string $pattern = '#^https://preciominimo\.chascity\.com/verificaprecio/[^?]+?\?sku=[^&]+$#';

    protected int $cooldown = 180;

    public function resolveProduct(): ?Product
    {
        $storeSlug = basename(parse_url($this->url->href, PHP_URL_PATH));
        parse_str(parse_url($this->url->href, PHP_URL_QUERY), $query);
        $productSku = $query['sku'];

        $store = Store::whereCountry('mx')->whereSlug($storeSlug)->first();

        if ($store) {
            return $store->products()->whereSku($productSku)->first();
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
                $this->hit(Response::HTTP_ALREADY_REPORTED);

                return true;
            }
        }

        return false;
    }

    protected function parse(Crawler $dom): int
    {
        $data = $dom->filter('.table-striped > tbody');

        // the product wasn't found on the page
        if ($data->count() == 0) {
            $this->cooldown = 1;
            
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

        foreach ($prices as $price) {
            $product->prices()->create([
                'price' => $price[0],
                'source' => 'chascity',
                'priced_at' => new Carbon($price[1]),
            ]);
        }

        // good; do not re-crawl anytime soon
        return Response::HTTP_ALREADY_REPORTED;
    }
}
