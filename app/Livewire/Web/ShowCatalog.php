<?php

namespace App\Livewire\Web;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
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
            ->with(['store', 'categories'])
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
            $brandSlug = $storeSlug;
        }

        $query->where(function (Builder $q) use ($categorySlug, $brandSlug) {
            if ($categorySlug) {
                $categories = Category::whereSlugTree($categorySlug)->get();
                $categoryIds = $categories->pluck('id')->all();

                $q->orWhereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });

                if ($categories->where('slug', $categorySlug)->count()) {
                    $this->title[] = $categories->where('slug', $categorySlug)->first()->title;
                }
            }

            if ($brandSlug) {
                $q->orWhere('brand_slug', $brandSlug);
                $brand = Product::whereBrandSlug($brandSlug)->first();

                if ($brand) {
                    $this->title[] = $brand->brand;
                }
            }
        });

        return view('livewire.web.show-catalog')->with([
            'store' => $store,
            'title' => implode(' / ', array_unique($this->title)),
            'products' => $query->paginate(30),
        ]);
    }
}
