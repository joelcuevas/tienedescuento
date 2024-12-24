<?php

namespace App\Crawlers;

abstract class JsonBaseCrawler extends BaseCrawler
{
    abstract protected function parse(mixed $json): int;

    protected function formatBody(string $body): mixed
    {
        return json_decode($body);
    }
}
