<?php

namespace App\Crawlers\Sears;

use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SearsCategoryCrawler extends SearsBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.sears\.com\.mx/cat/(.+)\?id=(\d+)$#';

    protected string $method = 'post';

    protected function transformCrawlingUrl(string $href): string
    {
        return 'https://6m62u1zbku-dsn.algolia.net/1/indexes/*/queries?x-algolia-agent=Algolia%20for%20JavaScript%20(4.24.0)%3B%20Browser%20(lite)%3B%20JS%20Helper%20(3.22.6)&x-algolia-api-key=6698ccede119391b5f6db5c39352b1f2&x-algolia-application-id=6M62U1ZBKU';
    }

    protected function getPostBody(): string
    {
        preg_match('#/cat/(.+)\?id=#', $this->url->href, $matches);
        $category = str_replace('/', ' > ', $matches[1]);
        $level = count(explode(' > ', $category)) - 1;
        $queryParams = 'facetFilters='.urlencode('[["hirerarchical_category.lvl'.$level.':'.$category.'"]]');

        return json_encode([
            'requests' => [
                ['indexName' => 'sears', 'params' => $queryParams],
            ],
        ]);
    }

    protected function parse(mixed $json): int
    {
        // check if there is processable data on the page
        if (count($json->results[0]->hits) == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        foreach ($json->results[0]->hits as $hit) {
            if ($hit->stock > 0) {
                $slug = Str::slug($hit->title);

                $data['sku'] = $hit->objectID;
                $data['title'] = $hit->title;
                $data['brand'] = $hit->brand;
                $data['external_url'] = 'https://www.sears.com.mx/producto/'.$hit->objectID.'/'.$slug;
                $data['image_url'] = $hit->photos[0]->source;
                $data['price'] = $hit->sale_price ?? $hit->price;
                $data['categories'] = $this->getCategories($hit);

                $this->saveProduct($data, 'category');
            }
        }

        // we are done!
        return Response::HTTP_OK;
    }

    protected function getCategories(object $hit): array
    {
        $parentId = null;
        $codes = array_flip((array) $hit->categories);
        $hierarchy = (array) $hit->hirerarchical_category;
        $categoryLevel = reset($hierarchy[max(array_keys($hierarchy))]);
        $categories = explode(' > ', $categoryLevel);
        $path = '';

        // save the categories
        foreach ($categories as $slug) {
            $title = Str::title($slug);
            $code = $codes[$slug];
            $path .= '/'.$slug;
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
