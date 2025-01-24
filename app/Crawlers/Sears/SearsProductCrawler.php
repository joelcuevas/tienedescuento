<?php

namespace App\Crawlers\Sears;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SearsProductCrawler extends SearsBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.sears\.com\.mx/producto/([a-zA-Z0-9]+)/[a-zA-Z0-9\-]+$#';

    protected static ?int $skuPatternIndex = 1;

    protected function transformCrawlingUrl(string $href): string
    {
        preg_match(self::$pattern, $this->url->href, $matches);
        $sku = $matches[self::$skuPatternIndex];

        return 'https://seapi.sears.com.mx/products/v1/products/'.$sku;
    }

    protected function parse(mixed $json): int
    {
        // check if there is processable data on the page
        if (! isset($json->data->id)) {
            return Response::HTTP_NO_CONTENT;
        }

        // check if there is stock
        if (! isset($json->data->stock) || $json->data->stock <= 0) {
            return Response::HTTP_NOT_FOUND;
        }

        // save the product
        $slug = Str::slug($json->data->title);

        $data['sku'] = $json->data->id;
        $data['title'] = $json->data->title;
        $data['brand'] = $json->data->brand;
        $data['external_url'] = 'https://www.sears.com.mx/producto/'.$json->data->id.'/'.$slug;
        $data['image_url'] = $json->data->pictures[0]->source;
        $data['price'] = $json->data->pricing->sales_price ?? $json->data->pricing->list_price;
        $data['categories'] = $this->getCategories((array) $json->data->categories[0]);

        $this->saveProduct($data, 'product');

        // we are done!
        return Response::HTTP_OK;
    }

    protected function getCategories(array $categories): array
    {
        usort($categories, fn ($a, $b) => $a->level <=> $b->level);

        $parentId = null;
        $path = '';

        // save the categories
        foreach ($categories as $cat) {
            $title = $cat->name;
            $code = $cat->id;
            $path .= '/'.$cat->seo;
            $externalUrl = 'https://www.sears.com.mx/cat'.$path.'?id='.$code;

            $category = Category::firstOrCreate([
                'store_id' => $this->store->id,
                'code' => $code,
            ], [
                'title' => $title,
                'external_url' => $externalUrl,
                'parent_id' => $parentId,
            ]);

            $parentId = $category->id;
        }

        // return only the last category
        return [$category];
    }
}
