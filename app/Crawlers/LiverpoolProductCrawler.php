<?php

namespace App\Crawlers;

use App\Models\Enums\UrlCooldown;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolProductCrawler extends LiverpoolBaseCrawler
{
    protected string $pattern = '#^https://www\.liverpool\.com\.mx/tienda/pdp/.*$#';

    protected function parse(Crawler $dom): UrlCooldown
    {
        $data = $dom->filter('#__NEXT_DATA__');

        if ($data->count() == 0) {
            return UrlCooldown::MALFORMED_PAGE;
        }

        $results = json_decode($data->text());

        if (! isset($results->query->data->mainContent)) {
            return UrlCooldown::MALFORMED_PAGE;
        }

        // this is a product details page, save the record
        $mainContent = $results->query->data->mainContent;

        if (isset($mainContent->records)) {
            foreach ($mainContent->records as $record) {
                $this->saveProduct($record);
            }
        }

        // everything is ok; this was a mid crawling-cost page
        return UrlCooldown::MID_COST_PAGE;
    }
}
