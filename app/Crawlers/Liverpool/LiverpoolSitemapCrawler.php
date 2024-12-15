<?php

namespace App\Crawlers\Liverpool;

use App\Jobs\ResolveUrl;
use App\Models\Url;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolSitemapCrawler extends LiverpoolBaseCrawler
{
    protected static string $pattern = '#^https://www\.liverpool\.com\.mx/sitemap/[^/]+\.xml$#';

    protected int $cooldown = 1;

    protected function parse(Crawler $dom): int
    {
        // this is a sitemap, store the urls for future crawling
        $dom->filterXPath('//loc')->each(function (Crawler $node) {
            ResolveUrl::dispatch($node->text());
        });

        return Response::HTTP_OK;
    }
}
