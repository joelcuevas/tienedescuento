<?php

namespace App\Crawlers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;

abstract class LiverpoolBaseCrawler extends BaseCrawler
{
    protected bool $proxied = true;

    protected Store $store;

    protected function setup(): void
    {
        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => 'liverpool',
        ], [
            'name' => 'Liverpool',
        ]);
    }

    protected function saveProduct($record): void
    {
        $meta = $record?->allMeta;

        if ($meta && $meta?->id) {
            $price = $meta?->minimumPromoPrice ?? $meta?->minimumListPrice;

            if ($price) {
                $url = 'https://www.liverpool.com.mx/tienda/pdp/-/'.$meta->id;
                $imageUrl = $this->getImageUrl($meta);

                $product = Product::updateOrCreate([
                    'store_id' => $this->store->id,
                    'sku' => $meta->id,
                ], [
                    'brand' => $meta->brand ?? 'NA',
                    'title' => $meta->title,
                    'url' => $url,
                    'image_url' => $imageUrl,
                ]);

                $product->prices()->create([
                    'price' => $price,
                    'source' => 'liverpool',
                ]);

                $categories = $this->getCategories($meta);
                $product->categories()->sync($categories);
            }
        }
    }

    private function getImageUrl(object $meta): ?string
    {
        if (isset($meta->productImages[0])) {
            foreach ($meta->productImages as $img) {
                if (isset($img->thumbnailImage)) {
                    return $img->thumbnailImage;
                }
            }
        }
    }

    private function getCategories(object $meta): array
    {
        $categoryLeafs = [];

        if (isset($meta->categoryBreadCrumbs)) {
            $first = $meta->categoryBreadCrumbs[0] ?? null;
            $breadcrumbs = [];

            if (is_string($first)) {
                // on the category page, the breadcrumbs are a string in the following struct:
                // CAT1#Title1>CAT2#Title2>CAT3#Title3...
                foreach ($meta->categoryBreadCrumbs as $breadcrumb) {
                    $categories = explode('>', $breadcrumb);
                    $set = [];

                    foreach ($categories as $category) {
                        [$code, $title] = explode('#', $category);
                        $set[] = (object) ['categoryId' => $code, 'categoryName' => $title];
                    }

                    $breadcrumbs[] = $set;
                }
            } else {
                // on the product page, there is an object array with a single category
                $breadcrumbs = [$meta->categoryBreadCrumbs];
            }

            foreach ($breadcrumbs as $breadcrumb) {
                $parentId = null;

                foreach ($breadcrumb as $item) {
                    $code = $item->categoryId;
                    $title = $item->categoryName;

                    $category = Category::firstOrCreate([
                        'store_id' => $this->store->id,
                        'code' => $code,
                    ], [
                        'title' => $title,
                        'url' => 'https://www.liverpool.com.mx/tienda/-/'.$code,
                        'parent_id' => $parentId,
                    ]);

                    $parentId = $category->id;
                }

                // keep only the last category of each breadcrumb
                $categoryLeafs[] = $category;
            }
        }

        return $categoryLeafs;
    }
}
