<?php

namespace App\Crawlers\Liverpool;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;

class LiverpoolProductCrawler extends LiverpoolBaseCrawler
{
    protected static string $pattern = '#^https://www\.liverpool\.com\.mx/tienda/pdp/(?:.+/)?(\d+)(?:\?.*)?$#';

    protected int $cooldown = 2;

    protected function parse(mixed $dom): int
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

        // yes, there is! save the product
        $mainContent = $results->query->data->mainContent;

        if (isset($mainContent->records)) {
            foreach ($mainContent->records as $record) {
                $this->saveProduct($record, 'product');
            }
        }

        // aaand... done!
        return Response::HTTP_OK;
    }
}
