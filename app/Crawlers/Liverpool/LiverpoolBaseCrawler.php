<?php

namespace App\Crawlers\Liverpool;

use App\Crawlers\WebBaseCrawler;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Support\Str;

abstract class LiverpoolBaseCrawler extends WebBaseCrawler
{
    protected Store $store;

    protected string $domain;

    protected string $name;

    protected string $slug;

    protected array $headers = [
        'User-Agent' => 'PostmanRuntime/7.42.0',
        'Accept' => '*/*',
    ];

    protected function setup(): void
    {
        if (static::$storeCode == 'liverpool-mx') {
            $this->domain = 'https://www.liverpool.com.mx';
            $this->name = 'Liverpool';
            $this->slug = 'liverpool';
        } else {
            $this->domain = 'https://www.suburbia.com.mx';
            $this->name = 'Suburbia';
            $this->slug = 'suburbia';
        }

        $this->store = Store::firstOrCreate([
            'country' => 'mx',
            'slug' => $this->slug,
        ], [
            'name' => $this->name,
            'external_url' => $this->domain,
        ]);
    }

    protected function saveProduct(mixed $record, string $source): ?Product
    {
        $meta = $record?->allMeta;

        if ($meta && $meta?->id) {
            $price = $meta?->minimumPromoPrice ?? $meta?->minimumListPrice;

            if ($price) {
                $title = strip_tags($meta->title);
                $slug = Str::slug($title);
                $externalUrl = $this->domain."/tienda/pdp/{$slug}/{$meta->id}";
                $imageUrl = $this->getImageUrl($meta);

                if ($source == 'product') {
                    $externalUrl = $this->url->href;
                }

                $url = Url::resolve($externalUrl, 30);

                if ($source == 'category') {
                    $url?->delay();
                }

                $product = Product::whereStoreId($this->store->id)->whereSku($meta->id)->first();

                if (! $product) {
                    $product = Product::create([
                        'store_id' => $this->store->id,
                        'sku' => $meta->id,
                        'brand' => $this->getBrand($meta),
                        'title' => ucwords($title),
                        'url_id' => $url?->id,
                        'external_url' => $externalUrl,
                        'image_url' => $imageUrl,
                    ]);

                    $categories = $this->getCategories($meta);
                    $product->categories()->syncWithoutDetaching($categories);
                }

                Product::withoutSyncingToSearch(function () use ($product, $price, $source) {
                    $product->prices()->create([
                        'price' => $price,
                        'source' => $this->slug.'-'.$source,
                    ]);
                });

                return $product;
            }
        }

        return null;
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
                        'external_url' => $this->domain."/tienda/{$slug}/{$code}",
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

    public function getBrand(object $meta): ?string
    {
        if (! isset($meta->brand) || ! $meta->brand) {
            return null;
        }

        $brand = $meta->brand;
        $title = $meta->title;
        $lowerBrand = strtolower($brand);
        $lowerTitle = strtolower($title);

        if (Str::contains($lowerTitle, $lowerBrand)) {
            $lowerBrand = preg_quote($lowerBrand, '/');
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
