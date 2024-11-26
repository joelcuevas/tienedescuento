<?php

namespace App\Jobs\Chascity;

use App\Models\Store;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class FetchProduct implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $store,
        private string $sku,
    ) {}

    public function handle(): void
    {
        $store = Store::whereCountry('mx')->whereSlug($this->store)->firstOrFail();
        $product = $store->products()->whereSku($this->sku)->first();

        if ($product && $product->prices->where('source', 'chascity')->count() == 0) {
            $url = 'https://preciominimo.chascity.com/verificaprecio/%s?sku=%s';
            $url = sprintf($url, $this->store, $this->sku);

            $response = Http::withHeaders([
                'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
                'Accept' => '*/*',
            ])->get($url);

            if ($response->status() == 200) {
                $dom = new Crawler($response->body());
                $data = $dom->filter('.table-striped > tbody');

                if ($data->count() > 0) {
                    $prices = $data->filter('tr')->each(function(Crawler $tr) {
                        $tds = $tr->filter('td');
                        $dateRaw = $tds->eq(0)->text();
                        $priceRaw = $tds->eq(1)->text();

                        $randomSeconds = rand(-30 * 24 * 60 * 60, 30 * 24 * 60 * 60);
                        $date = (new Carbon($dateRaw))->addSeconds($randomSeconds);
                        $price = (float)str_replace(['$', ','], '', $priceRaw);

                        return [$price, $date];
                    });

                    foreach ($prices as $price) {
                        $product->addPrice($price[0], 'chascity', $price[1]);
                    }
                }
            }
        }
    }
}
