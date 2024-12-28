<?php

namespace App\Crawlers\Liverpool;

use App\Models\Product;
use Illuminate\Http\Response;

class LiverpoolProductCrawler extends LiverpoolBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.liverpool\.com\.mx/tienda/pdp/(?:.+/)?(\d+)(?:\?.*)?$#';

    protected static ?int $skuPatternIndex = 1;

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

        // there is procesable data! save the product
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
