<?php

namespace App\Crawlers;

use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

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

    abstract protected function parse(mixed $body): int;

    abstract protected function formatBody(string $body): mixed;

    public function crawl(): void
    {
        $this->startingTime = microtime(true);

        if (! $this->matchesPattern($this->url->href)) {
            $this->url->release();

            return;
        }

        $this->setup();

        $crawledToday = config('app.env') != 'local' && $this->url->crawled_at >= now()->startOfDay();

        if ($crawledToday || $this->recentlyCrawled()) {
            if ($this->url->crawled_at == null) {
                $this->hit(Response::HTTP_ALREADY_REPORTED);
            } else {
                $this->url->release();
            }

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
                $body = $this->formatBody($response->body());

                if ($body == null) {
                    $status = Response::HTTP_GONE;
                } else {
                    $status = $this->parse($body);
                }
            }
        } catch (HttpClientException $e) {
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
