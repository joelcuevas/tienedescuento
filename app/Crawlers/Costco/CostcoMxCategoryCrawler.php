<?php

namespace App\Crawlers\Costco;

use App\Models\Category;
use App\Models\Url;
use Illuminate\Http\Response;

class CostcoMxCategoryCrawler extends CostcoMxBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.costco\.com\.mx/rest/v2/mexico/products/search(\?.+)?$#';

    protected function parse(mixed $json): int
    {
        // check if there is processable data on the page
        if (! isset($json->products) || count($json->products) == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        // get the page category and iterate over the products
        $category = $this->getCategory($json);

        foreach ($json->products as $product) {
            if ($product?->stock?->stockLevelStatus == 'inStock') {
                if (isset($product->price->value)) {
                    $data['sku'] = $product->code;
                    $data['title'] = $product->name;
                    $data['external_url'] = 'https://www.costco.com.mx'.$product->url;
                    $data['image_url'] = $this->getImageUrl($product);
                    $data['price'] = $product->price->value;
                    $data['categories'] = [$category];

                    $this->saveProduct($data, 'category');
                }
            }
        }

        // if there are more pages, resolve the next one
        if ($json->pagination->currentPage < $json->pagination->totalPages - 1) {
            $page = $json->pagination->currentPage + 1;
            $href = preg_replace('/currentPage=\d+/', 'currentPage='.$page, $this->url->href);
            $nextUrl = Url::resolve($href);

            // force-discover the next page
            if ($nextUrl->status != Response::HTTP_OK) {
                $nextUrl->scheduleNow();
            }
        }

        // we are done!
        return Response::HTTP_OK;
    }
}
