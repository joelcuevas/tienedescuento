<?php

namespace App\Crawlers\Costco;

use App\Crawlers\BaseCrawler;
use App\Models\Url;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class CostcoMxCategoryCrawler extends BaseCrawler
{
    protected static string $pattern = '#^https://www\.costco\.com\.mx(?:/[\w\-]+)*/c/([\w\.\-]+)(?:\?page=(\d+))?$#';

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
            //$this->saveProduct($record, 'category');
        }

        // if there are more pages, resolve the next one
        if (isset($mainContent->pageInfo)) {
            $noOfPages = $mainContent->pageInfo->noOfPages;

            if ($noOfPages > 0) {
                $currentPage = $mainContent->pageInfo->currentPage ?? 1;

                if ($currentPage < $noOfPages) {
                    $href = preg_replace('/\/page-\d+$/', '', $this->url->href);
                    $url = Url::resolve($href.'/page-'.($currentPage + 1));

                    // force-discover the next page
                    if ($url->status != Response::HTTP_OK) {
                        $url->scheduleNow();
                    }
                }
            }
        }

        // we are done!
        return Response::HTTP_OK;
    }
}
