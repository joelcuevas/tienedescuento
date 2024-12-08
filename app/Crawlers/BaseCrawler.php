<?php

namespace App\Crawlers;

use App\Models\Enums\UrlCooldown;
use App\Models\Product;
use App\Models\Url;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseCrawler
{
    protected string $pattern = '';

    protected bool $proxied = false;

    protected array $headers = [
        'User-Agent' => 'PostmanRuntime/7.42.0',
        'Accept' => '*/*',
    ];

    public function __construct(
        protected string $url,
    ) {}

    protected function setup(): void {}

    public function matchesPattern(): bool
    {
        return $this->pattern ? preg_match($this->pattern, $this->url) : false;
    }

    public function resolveProduct(): ?Product
    {
        return null;
    }

    protected function recentlyCrawled(): bool
    {
        return false;
    }

    abstract protected function parse(Crawler $dom): UrlCooldown;

    public function crawl(): void
    {
        if (! $this->matchesPattern($this->url)) {
            return;
        }

        $this->setup();

        $url = Url::firstOrCreate([
            'hash' => sha1($this->url),
        ], [
            'href' => $this->url,
            'domain' => parse_url($this->url, PHP_URL_HOST),
            'scheduled_at' => now()->subMinutes(1),
        ]);

        if ($this->recentlyCrawled()) {
            $url->hit(304, UrlCooldown::NOT_MODIFIED);

            return;
        }

        $status = 503;
        $cooldown = UrlCooldown::TOMORROW;

        try {
            $href = $this->proxied ? $url->proxied() : $url->href;
            $response = Http::withHeaders($this->headers)->get($href);

            $status = $response->status();

            if ($status == 200) {
                $dom = new Crawler($response->body());

                if ($dom !== null) {
                    $cooldown = $this->parse($dom);
                }
            }
        } catch (HttpClientException $e) {
            // something went wrong while connecting to the url...
            // schedule a re-check with a 503 (unavailable) status
            $status = 503;
            $cooldown = UrlCooldown::TOMORROW;
        }

        $url->hit($status, $cooldown);
    }
}
