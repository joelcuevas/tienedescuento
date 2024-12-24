<?php

namespace App\Crawlers\Costco;

use App\Crawlers\WebBaseCrawler;
use App\Jobs\ResolveUrl;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class CostcoMxSitemapCrawler extends WebBaseCrawler
{
    protected static string $pattern = '#^https:\/\/www\.costco\.com\.mx\/sitemap.*\.xml$#';

    protected int $cooldown = 1;

    protected string $baseUrl = 'https://www.costco.com.mx/rest/v2/mexico/products/search?category=%s&currentPage=0&pageSize=100&lang=es_MX&curr=MXN&fields=FULL';

    protected function parse(mixed $dom): int
    {
        // this is a sitemap, store the urls for future crawling
        $dom->registerNamespace('ns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $dom->filterXPath('//ns:loc')->each(function (Crawler $node) {
            preg_match('#/c/([^/]+)$#', $node->text(), $matches);
            $categoryCode = $matches[1] ?? null;

            if ($categoryCode) {
                $href = sprintf($this->baseUrl, $categoryCode);
                ResolveUrl::dispatch($href);
            }
        });

        return Response::HTTP_OK;
    }
}
