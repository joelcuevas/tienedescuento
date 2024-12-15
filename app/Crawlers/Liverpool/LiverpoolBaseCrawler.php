<?php

namespace App\Crawlers\Liverpool;

use App\Crawlers\BaseCrawler;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Str;

abstract class LiverpoolBaseCrawler extends BaseCrawler
{
    protected Store $store;

    protected function setup(): void
    {
        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => 'liverpool',
        ], [
            'name' => 'Liverpool',
            'url' => 'https://www.liverpool.com.mx',
        ]);
    }

    protected function saveProduct(mixed $record, string $source): void
    {
        $meta = $record?->allMeta;

        if ($meta && $meta?->id) {
            $price = $meta?->minimumPromoPrice ?? $meta?->minimumListPrice;

            if ($price) {
                $slug = Str::of($meta->title)->lower()->replace(' ', '-');
                $url = "https://www.liverpool.com.mx/tienda/pdp/{$slug}/{$meta->id}";
                $imageUrl = $this->getImageUrl($meta);

                $product = Product::updateOrCreate([
                    'store_id' => $this->store->id,
                    'sku' => $meta->id,
                ], [
                    'brand' => $this->getBrand($meta),
                    'title' => ucwords(strip_tags($meta->title)),
                    'url' => $url,
                    'image_url' => $imageUrl,
                ]);

                $product->prices()->create([
                    'price' => $price,
                    'source' => 'liverpool-'.$source,
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
                if (isset($img->smallImage)) {
                    return $img->smallImage;
                }
            }
        }

        if (isset($meta->galleryImagesVariants[0])) {
            return $meta->galleryImagesVariants[0];
        }

        if (isset($meta->variants[0]->smallImage)) {
            return $meta->variants[0]->smallImage;
        }

        return 'https://placehold.co/400?text=Not%20Found';
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

            // save the categories
            foreach ($breadcrumbs as $breadcrumb) {
                $parentId = null;

                foreach ($breadcrumb as $item) {
                    $code = $item->categoryId;
                    $title = strip_tags($item->categoryName);
                    $slug = Str::of($title)->lower()->replace(' ', '-');

                    $category = Category::firstOrCreate([
                        'store_id' => $this->store->id,
                        'code' => $code,
                    ], [
                        'title' => $title,
                        'url' => "https://www.liverpool.com.mx/tienda/{$slug}/{$code}",
                        'parent_id' => $parentId,
                    ]);

                    $parentId = $category->id;
                }

                // return only the last category of each breadcrumb for the product
                $categoryLeafs[] = $category;
            }
        }

        return $categoryLeafs;
    }

    function getBrand(object $meta): string
    {
        $brand = $meta->brand ?? __('Unknown');
        $title = $meta->title;
        $lowerBrand = strtolower($brand);
        $lowerTitle = strtolower($title);

        if (Str::contains($lowerTitle, $lowerBrand)) {
            preg_match("/\b{$lowerBrand}\b/i", $title, $matches);

            if (count($matches)) {
                return $matches[0];
            }
        }

        if (strlen($brand) > 3) {
            return Str::title($brand);
        }

        return $brand;
    }
}
