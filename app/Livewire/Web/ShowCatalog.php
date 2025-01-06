<?php

namespace App\Livewire\Web;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class ShowCatalog extends Component
{
    use WithPagination;

    private array $title = [];

    public function render()
    {
        $countryCode = request()->countryCode;

        // only search into current country stores
        $query = Product::query()
            ->whereHas('store', function ($query) use ($countryCode) {
                $query->where('stores.country', $countryCode);
            })
            ->with(['store'])
            ->recent()
            ->orderByDesc('discount')
            ->limit(360);

        $storeSlug = request()->storeSlug;
        $categorySlug = request()->categorySlug;
        $brandSlug = request()->brandSlug;
        $taxonomySlug = null;

        // if there is a store slug, show the store catalog
        $store = Store::whereCountry($countryCode)->whereSlug($storeSlug)->first();

        if ($store) {
            $query->whereStoreId($store->id);
        } else {
            // if no store is found, asume it's a taxonomy
            $taxonomySlug = $storeSlug;
            $categorySlug = null;
            $brandSlug = null;
        }

        if ($taxonomySlug) {
            $query->whereTaxonomy($countryCode, $taxonomySlug);
            $taxonomy = Category::whereSlug($taxonomySlug)->first();

            if ($taxonomy) {
                $this->title[] = $taxonomy->title;
            }
        }

        if ($categorySlug) {
            $query->whereCategory($categorySlug);
            $category = Category::whereSlug($categorySlug)->first();

            if ($category) {
                $this->title[] = $category->title;
            }
        }

        if ($brandSlug) {
            $query->where('brand_slug', $brandSlug);
            $brand = Product::whereBrandSlug($brandSlug)->first();

            if ($brand) {
                $this->title[] = $brand->brand;
            }
        }

        return view('livewire.web.show-catalog')->with([
            'store' => $store,
            'title' => implode(' / ', array_unique($this->title)),
            'products' => $query->limitedPaginate(Product::PAGE_SIZE),
        ]);
    }
}
