<?php

namespace App\Crawlers\Palacio;

use Illuminate\Http\Response;

class PalacioProductCrawler extends PalacioBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.elpalaciodehierro\.com/[\w\-]+-([\w]+)\.html(\?.*)?$#';

    protected static ?int $skuPatternIndex = 1;

    protected function parse(mixed $dom): int
    {
        $data = $dom->filter('[itemscope][itemtype="http://schema.org/Product"]');

        // check if there is processable data on the page
        if ($data->count() == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        $categories = $this->getCategories($dom->filter('.b-pdp_breadcrumbs'));

        $this->saveProduct($data->eq(0), $categories, 'product');

        // aaand... done!
        return Response::HTTP_OK;
    }
}
