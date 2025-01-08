<?php

namespace App\Crawlers\Palacio;

use App\Jobs\ResolveUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class PalacioSitemapCrawler extends PalacioBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.elpalaciodehierro\.com/sitemap_.*\.xml$#';

    protected function parse(mixed $dom): int
    {
        $dom->registerNamespace('ns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $dom->filterXPath('//ns:loc')->each(function (Crawler $node) {
            $href = $node->text();

            $priority = Str::endsWith($href, '.xml') ? 10 : 20;
            ResolveUrl::dispatch($href, $priority);
        });

        return Response::HTTP_OK;
    }
}
