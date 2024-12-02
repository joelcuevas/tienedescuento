<?php

namespace App\Crawlers;

use App\Models\Enums\UrlCooldown;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class ChascityProductCrawler extends BaseCrawler
{
    protected string $pattern = '#^https://preciominimo\.chascity\.com/verificaprecio/[^?]+?\?sku=[^&]+$#';

    protected ?Store $store = null;

    protected ?Product $product = null;

    protected function setup(): void
    {
        $storeSlug = basename(parse_url($this->url, PHP_URL_PATH));
        parse_str(parse_url($this->url, PHP_URL_QUERY), $query);
        $productSku = $query['sku'];

        $this->store = Store::whereCountry('mx')->whereSlug($storeSlug)->first();

        if ($this->store) {
            $this->product = $this->store->products()->whereSku($productSku)->first();
        }
    }

    protected function allowed(): bool
    {
        $p = $this->product;

        return $p && $p->prices->where('source', 'chascity')->count() == 0;
    }

    protected function parse(Crawler $dom): UrlCooldown
    {
        $data = $dom->filter('.table-striped > tbody');

        // if the product wasn't found, schedule a sanity check
        if ($data->count() == 0) {
            return UrlCooldown::SANITY_CHECK;
        }

        $prices = $data->filter('tr')->each(function (Crawler $tr) {
            $tds = $tr->filter('td');
            $date = $tds->eq(0)->text();
            $priceRaw = $tds->eq(1)->text();
            $price = (float) str_replace(['$', ','], '', $priceRaw);

            return [$price, $date];
        });

        foreach ($prices as $price) {
            $this->product->prices()->create([
                'price' => $price[0],
                'source' => 'chascity',
                'priced_at' => new Carbon($price[1]),
            ]);
        }

        // if the product was found, don't recrawl again for past prices
        return UrlCooldown::NO_RECRAWL;
    }
}
