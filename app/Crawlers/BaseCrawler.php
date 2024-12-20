<?php

namespace App\Crawlers;

use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseCrawler
{
    protected static string $pattern = '';

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

    public function resolveProduct(): ?Product
    {
        return null;
    }

    protected function recentlyCrawled(): bool
    {
        return false;
    }

    abstract protected function parse(Crawler $dom): int;

    public function crawl(): void
    {
        $this->startingTime = microtime(true);

        if (! $this->matchesPattern($this->url->href)) {
            $this->url->release();

            return;
        }

        $this->setup();

        $crawledToday = $this->url->crawled_at >= now()->startOfDay();

        if ($crawledToday || $this->recentlyCrawled()) {
            $this->url->release();

            return;
        }

        try {
            $href = $this->url->href;
            $proxied = $this->url->getCrawlerConfig('proxied', false);

            if ($proxied) {
                $href = config('crawler.proxy_url', '').$this->url->href;
            }

            $response = Http::withHeaders($this->headers)->get($href);
            $status = $response->status();

            if ($status == Response::HTTP_OK) {
                $dom = new Crawler($response->body());

                if ($dom !== null) {
                    $status = $this->parse($dom);
                }
            }
        } catch (HttpClientException $e) {
            // something went wrong while connecting to the url
            $status = Response::HTTP_SERVICE_UNAVAILABLE;
        } finally {
            $this->hit($status);
        }
    }

    protected function hit($status)
    {
        if ($this->url) {
            $startingTime = $this->startingTime ?? microtime(true);
            $this->url->hit($status, $this->cooldown, $startingTime);
        }
    }
}
