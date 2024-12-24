<?php

namespace App\Crawlers\Liverpool;

use App\Models\Url;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolCategoryCrawler extends LiverpoolBaseCrawler
{
    protected static string $pattern = '#^https:\/\/www\.liverpool\.com\.mx\/tienda\/[^\/]+\/[^\/]+(\/page-\d+)?$#';

    protected int $cooldown = 1;

    protected function parse(Crawler $dom): int
    {
        $data = $dom->filter('#__NEXT_DATA__');

        // check if there is processable data on the page
        if ($data->count() == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        $results = json_decode($data->text());

        if (! isset($results->query->data->mainContent)) {
            return Response::HTTP_NO_CONTENT;
        }

        // yes, there is!
        $mainContent = $results->query->data->mainContent;

        // but this is a category showcase page; do not process
        if (! isset($mainContent->records)) {
            return Response::HTTP_NO_CONTENT;
        }

        // this is a product listing page, now save the records
        foreach ($mainContent->records as $record) {
            $this->saveProduct($record, 'category');
        }

        // if there are more pages, resolve the next one
        if (isset($mainContent->pageInfo)) {
            $noOfPages = $mainContent->pageInfo->noOfPages;

            if ($noOfPages > 0) {
                $currentPage = $mainContent->pageInfo->currentPage ?? 1;

                if ($currentPage < $noOfPages) {
                    $href = preg_replace('/\/page-\d+$/', '', $this->url->href);
                    $nextUrl = Url::resolve($href.'/page-'.($currentPage + 1));

                    // force-discover the next page
                    if ($nextUrl->status != Response::HTTP_OK) {
                        $nextUrl->scheduleNow();
                    }
                }
            }
        }

        // we are done!
        return Response::HTTP_OK;
    }
}
