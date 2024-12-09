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
            $url = $node->text();
            $scheduledAt = now()->subMinutes(1);

            if (str_contains($url, '/pdp/')) {
                $scheduledAt = now()->addHours(12);
            }

            logger()->channel('stderr')->debug($url);

            Url::firstOrCreate([
                'hash' => sha1($url),
            ], [
                'href' => $url,
                'domain' => parse_url($url, PHP_URL_HOST),
                'scheduled_at' => $scheduledAt,
            ]);
        });

        // everything was ok; this was a low crawling-cost page
        return UrlCooldown::LOW_COST_PAGE;
    }
}
