<?php

namespace App\Crawlers\Sears;

use App\Crawlers\WebBaseCrawler;
use App\Jobs\ResolveUrl;
use App\Models\Url;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class SearsSitemapCrawler extends WebBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.sears\.com\.mx/.*\.xml$#';

    protected function parse(mixed $dom): int
    {
        $dom->registerNamespace('ns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $dom->filterXPath('//ns:loc')->each(function (Crawler $node) {
            $href = $node->text();

            $isSitemap = Str::endsWith($href, '.xml');
            $priority = $isSitemap ? 10 : 20;

            if ($isSitemap) {
                ResolveUrl::dispatch($href, $priority);
            } else {
                dispatch(function () use ($href, $priority) {
                    $response = $this->makeRequest($href);
                    $status = $response->status();

                    if ($status == Response::HTTP_OK) {
                        $dom = $this->formatBody($response->body());
                        $canonical = $dom->filter('link[rel="canonical"]')->first();

                        if ($canonical->count() > 0) {
                            $href = $canonical->attr('href');
                            Url::resolve($href, $priority);
                        }
                    }
                });
            }
        });

        return Response::HTTP_OK;
    }
}
