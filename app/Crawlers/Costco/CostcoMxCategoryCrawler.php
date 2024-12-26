<?php

namespace App\Crawlers\Costco;

use App\Crawlers\JsonBaseCrawler;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Http\Response;

class CostcoMxCategoryCrawler extends JsonBaseCrawler
{
    protected static string $pattern = '#^https:\/\/www\.costco\.com\.mx\/rest\/v2\/mexico\/products\/search(\?.+)?$#';

    protected int $cooldown = 1;

    protected Store $store;

    protected Category $category;

    protected int $page = 0;

    protected function setup(): void
    {
        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => 'costco',
        ], [
            'name' => 'Costco',
            'external_url' => 'https://www.costco.com.mx',
        ]);
    }

    protected function parse(mixed $json): int
    {
        // check if there is processable data on the page
        if (! isset($json->products) || count($json->products) == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        $this->findCategories($json);

        foreach ($json->products as $product) {
            if ($product?->stock?->stockLevelStatus == 'inStock') {
                if (isset($product->price->value)) {
                    $data['sku'] = $product->code;
                    $data['title'] = $product->name;
                    $data['external_url'] = 'https://www.costco.com.mx'.$product->url;
                    $data['image_url'] = $this->getImageUrl($product);
                    $data['price'] = $product->price->value;

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

    protected function saveProduct(array $data, string $source): void
    {
        preg_match('#/p/([^/]+)$#', $data['external_url'], $matches);
        $sku = $matches[1];

        $price = (float) str_replace(['$', ','], '', $data['price']);

        $product = Product::updateOrCreate([
            'store_id' => $this->store->id,
            'sku' => $sku,
        ], [
            'brand' => null,
            'title' => strip_tags($data['title']),
            'external_url' => $data['external_url'],
            'image_url' => $data['image_url'],
        ]);

        $product->prices()->create([
            'price' => $price,
            'source' => 'costco-'.$source,
        ]);

        $product->categories()->syncWithoutDetaching($this->category);
    }

    protected function getImageUrl(object $product): ?string
    {
        if (isset($product->images)) {
            foreach ($product->images as $img) {
                if ($img?->format == 'results') {
                    return 'https://www.costco.com.mx'.$img->url;
                }
            }
        }

        return 'https://placehold.co/400?text=Not%20Found';
    }

    protected function findCategories(object $json): void
    {
        if (! isset($json->category->url)) {
            return;
        }

        $parentId = null;

        $categories[] = [
            'code' => $json->category->code,
            'title' => $json->category->name,
            'external_url' => 'https://www.costco.com.mx'.$json->category->url,
        ];

        if (isset($json->category->supercategories)) {
            foreach ($json->category->supercategories as $super) {
                $categories[] = [
                    'code' => $super->code,
                    'title' => $super->name,
                    'external_url' => 'https://www.costco.com.mx'.$super->url,
                ];
            }
        }

        $categories = array_reverse($categories);

        foreach ($categories as $cat) {
            $category = Category::firstOrCreate([
                'store_id' => $this->store->id,
                'code' => $cat['code'],
            ], [
                'title' => $cat['title'],
                'external_url' => $cat['external_url'],
                'parent_id' => $parentId,
            ]);

            $parentId = $category->id;
            $this->category = $category;
        }
    }
}
