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

    protected array $headers = [
        'User-Agent' => 'PostmanRuntime/7.42.0',
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
        $startingTime = microtime(true);

        if (! $this->matchesPattern($this->url->href)) {
            return;
        }

        $this->setup();

        if ($this->recentlyCrawled()) {
            $this->url->hit(Response::HTTP_ALREADY_REPORTED, $this->cooldown, $startingTime);

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
            // something went wrong while connecting
            $status = Response::HTTP_SERVICE_UNAVAILABLE;
        } finally {
            $this->url->hit($status, $this->cooldown, $startingTime);
        }
    }
}
