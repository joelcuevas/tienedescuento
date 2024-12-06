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

    public function matches(): bool
    {
        return $this->pattern ? preg_match($this->pattern, $this->url) : false;
    }

    public function exists(): ?Product
    {
        return null;
    }

    protected function setup(): void {}

    protected function allowed(): bool
    {
        return true;
    }

    abstract protected function parse(Crawler $dom): UrlCooldown;

    public function crawl(): void
    {
        if (! $this->matches($this->url)) {
            return;
        }

        $this->setup();

        if (! $this->allowed()) {
            return;
        }

        $url = Url::firstOrCreate([
            'href' => $this->url,
        ], [
            'scheduled_at' => now()->subMinutes(1),
        ]);

        if (! $url->follow()) {
            return;
        }

        $status = 503;
        $cooldown = UrlCooldown::SANITY_CHECK;

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
            // something went wrong while getting the page content...
            // schedule a sanity check with a 503 (unavailable) default status
        }

        $url->hit($status, $cooldown);
    }
}
