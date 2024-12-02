<?php

namespace App\Crawlers;

use App\Models\Enums\UrlCooldown;
use App\Models\Url;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolSitemapCrawler extends LiverpoolBaseCrawler
{
    protected string $pattern = '#^https://www\.liverpool\.com\.mx/sitemap/[^/]+\.xml$#';

    protected function parse(Crawler $dom): UrlCooldown
    {
        // this is a sitemap, just store the urls for future crawling
        $dom->filterXPath('//loc')->each(function (Crawler $node) {
            $loc = $node->text();

            Url::firstOrCreate([
                'href' => $loc,
            ], [
                'scheduled_at' => now()->subMinutes(1),
            ]);
        });

        // everything was ok; this was a low crawling-cost page
        return UrlCooldown::LOW_COST_PAGE;
    }
}
