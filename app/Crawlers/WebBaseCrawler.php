<?php

namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler;

abstract class WebBaseCrawler extends BaseCrawler
{
    abstract protected function parse(mixed $dom): int;

    protected function formatBody(string $body): mixed
    {
        return new Crawler($body);
    }
}
