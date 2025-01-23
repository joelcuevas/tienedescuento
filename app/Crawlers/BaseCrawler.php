<?php

namespace App\Crawlers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Sentry;

abstract class BaseCrawler
{
    protected static ?string $storeCode = null;

    protected static ?string $pattern = null;

    protected static ?int $skuPatternIndex = null;

    protected string $method = 'get';

    protected int $cooldown = 1;

    protected float $startingTime;

    protected array $headers = [
        'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Accept' => '*/*',
    ];

    public function __construct(
        protected Url $url,
    ) {}

    private function setupSentry()
    {
        Sentry\configureScope(function (Sentry\State\Scope $scope): void {
            $scope->setContext('URL', [
                'id' => $this->url->id,
                'href' => $this->url->href,
            ]);

            $scope->setTag('crawler', $this->url->crawler_class);
        });
    }

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

    public function resolveProduct(): ?Product
    {
        return static::matchesProduct($this->url->href);
    }

    abstract protected function parse(mixed $body): int;

    abstract protected function formatBody(string $body): mixed;

    public function crawl(): void
    {
        $this->setupSentry();

        $this->startingTime = microtime(true);

        if (! $this->matchesPattern($this->url->href)) {
            $this->url->release();

            return;
        }

        $this->setup();

        try {
            $href = $this->transformCrawlingUrl($this->url->href);
            $proxied = $this->url->getCrawlerConfig('proxied', false);

            if ($proxied) {
                $href = config('crawlers.proxy_url', '').$this->url->href;
            }

            $response = $this->makeRequest($href);
            $status = $response->status();

            if ($status == Response::HTTP_OK) {
                $body = $this->formatBody($response->body());

                if ($body == null) {
                    $status = Response::HTTP_EXPECTATION_FAILED;
                } else {
                    $status = $this->parse($body);
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
        }
    }

    protected function makeRequest(string $href): ClientResponse
    {
        $http = Http::withHeaders($this->headers);

        if ($this->method == 'post') {
            $response = $http
                ->withBody(
                    $this->getPostBody(),
                    $this->getPostContentType(),
                )
                ->post($href);
        } else {
            $response = $http->get($href);
        }

        return $response;
    }

    protected function transformCrawlingUrl(string $href): string
    {
        return $href;
    }

    protected function getPostBody(): string
    {
        return '';
    }

    protected function getPostContentType(): string
    {
        return 'application/json';
    }
}
