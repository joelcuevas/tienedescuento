<?php

namespace App\Crawlers\Liverpool;

use App\Jobs\ResolveUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolSitemapCrawler extends LiverpoolBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.liverpool\.com\.mx/sitemap/[^/]+\.xml$#';

    protected array $ignore = ['/pdps-', '/ck-'];

    protected function parse(mixed $dom): int
    {
        // this is a sitemap, store the urls for future crawling
        $dom->filterXPath('//loc')->each(function (Crawler $node) {
            $href = $node->text();

            // ignore pdps for now
            if (Str::contains($href, $this->ignore)) {
                return;
            }

            ResolveUrl::dispatch($href, 10);
        });

        return Response::HTTP_OK;
    }
}
