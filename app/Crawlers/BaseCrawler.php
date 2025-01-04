<?php

namespace App\Crawlers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

abstract class BaseCrawler
{
    protected static ?string $storeCode = null;

    protected static ?string $pattern = null;

    protected static ?int $skuPatternIndex = null;

    protected int $cooldown = 1;

    protected float $startingTime;

    protected array $headers = [
        'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Accept' => '*/*',
    ];

    public function __construct(
        protected Url $url,
    ) {}

    protected function setup(): void {}

    public static function matchesPattern($url): bool
    {
        return preg_match(static::$pattern, $url);
    }

    public static function matchesProduct($href): ?Product
    {
        if (static::$skuPatternIndex !== null) {
            preg_match(static::$pattern, $href, $matches);

            if (isset($matches[static::$skuPatternIndex])) {
                $store = Store::whereCode(static::$storeCode)->first();

                if ($store) {
                    return Product::whereStoreId($store->id)->whereSku($matches[1])->first();
                }
            }
        }

        return null;
    }

    abstract protected function parse(mixed $body): int;

    abstract protected function formatBody(string $body): mixed;

    public function crawl(): void
    {
        start_profiling('URL: '.$this->url->id);
        $this->startingTime = microtime(true);

        if (! $this->matchesPattern($this->url->href)) {
            $this->url->release();

            return;
        }

        profile('URL: '.$this->url->id.' - Setting up crawler');
        $this->setup();
        profile('URL: '.$this->url->id.' - Crawler setup');

        try {
            $href = $this->url->href;
            $proxied = $this->url->getCrawlerConfig('proxied', false);

            if ($proxied) {
                $href = config('crawler.proxy_url', '').$this->url->href;
            }

            profile('URL: '.$this->url->id.' - Starting HTTP request');
            $response = Http::withHeaders($this->headers)->get($href);
            $status = $response->status();

            profile('URL: '.$this->url->id.' - HTTP request finished');

            if ($status == Response::HTTP_OK) {
                $body = $this->formatBody($response->body());

                if ($body == null) {
                    $status = Response::HTTP_GONE;
                } else {
                    profile('URL: '.$this->url->id.' - Parsing body');
                    $status = $this->parse($body);
                    profile('URL: '.$this->url->id.' - Body parsed');
                }
            }
        } catch (HttpClientException $e) {
            $status = Response::HTTP_SERVICE_UNAVAILABLE;
        }

        $this->hit($status);
    }

    protected function hit($status)
    {
        if ($this->url) {
            $startingTime = $this->startingTime ?? microtime(true);
            $this->url->hit($status, $this->cooldown, $startingTime);
            profile('URL: '.$this->url->id.' - URL hit');
        }
    }
}
