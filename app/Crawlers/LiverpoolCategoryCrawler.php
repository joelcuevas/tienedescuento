<?php

namespace App\Crawlers;

use App\Jobs\CrawlUrl;
use App\Models\Enums\UrlCooldown;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolCategoryCrawler extends LiverpoolBaseCrawler
{
    protected string $pattern = '#^https:\/\/www\.liverpool\.com\.mx\/tienda\/[^\/]+\/[^\/]+(\/page-\d+)?$#';

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

        $mainContent = $results->query->data->mainContent;

        if (isset($mainContent->records)) {
            // this is a product listing page, save the records
            foreach ($mainContent->records as $record) {
                $this->saveProduct($record);
            }
        } else {
            // this is a category landing page, schedule a sanity check
            return UrlCooldown::SANITY_CHECK;
        }

        // if there are more pages, follow the next one
        if (isset($mainContent->pageInfo)) {
            $noOfPages = $mainContent->pageInfo->noOfPages;

            if ($noOfPages > 0) {
                $currentPage = $mainContent->pageInfo->currentPage ?? 1;

                if ($currentPage < $noOfPages) {
                    $url = preg_replace('/\/page-\d+$/', '', $this->url);
                    $url = $url.'/page-'.($currentPage + 1);

                    CrawlUrl::dispatch($url)->onQueue('liverpool');
                }
            }
        }

        // everything is ok; this was a low crawling-cost page
        return UrlCooldown::LOW_COST_PAGE;
    }
}
