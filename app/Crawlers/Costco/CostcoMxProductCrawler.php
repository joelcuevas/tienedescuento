<?php

namespace App\Crawlers\Costco;

use App\Models\Product;
use Illuminate\Http\Response;

class CostcoMxProductCrawler extends CostcoMxBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.costco\.com\.mx/rest/v2/mexico/products/(?!search\b)([\w-]+)(?:[/?].*)?$#';

    protected static ?int $skuPatternIndex = 1;

    protected function parse(mixed $json): int
    {
        // check if there is processable data on the page
        if (! isset($json->code)) {
            return Response::HTTP_NO_CONTENT;
        }

        // check if there is stock
        if ($json?->stock?->stockLevel <= 0) {
            return Response::HTTP_GONE;
        }

        // save the product
        if (isset($json->price->value)) {
            $data['sku'] = $json->code;
            $data['title'] = $json->name;
            $data['external_url'] = 'https://www.costco.com.mx'.$json->url;
            $data['image_url'] = $this->getImageUrl($json);
            $data['price'] = $json->price->value;

            $category = $this->getCategory($json);
            $data['categories'] = [$category];

            $this->saveProduct($data, 'product');
        }

        // we are done!
        return Response::HTTP_OK;
    }
}
