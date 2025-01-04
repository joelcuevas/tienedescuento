<?php

namespace App\Livewire\Web;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Livewire\Component;

class ShowCatalog extends Component
{
    private array $title = [];

    public function render()
    {
        $countryCode = request()->countryCode;

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

        $store = Store::whereCountry($countryCode)->whereSlug($storeSlug)->first();

        if ($store) {
            $query->whereStoreId($store->id);
        } else {
            $categorySlug = $storeSlug;
            $brandSlug = null;
        }

        if ($categorySlug) {
            $query->whereCategory($categorySlug, 2);
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
            'products' => $query->paginate(36),
        ]);
    }
}
