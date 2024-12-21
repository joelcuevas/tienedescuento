<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\Store;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ShowCatalog extends Component
{
    public function render()
    {
        $store = Store::whereCountry(request()->countryCode)
            ->whereSlug(request()->storeSlug)
            ->firstOrFail();

        $products = $store->products()
            ->with(['store', 'categories'])
            ->orderByDesc('discount');

        $subtitle = null;
        $categorySlug = request()->categorySlug;
        $brandSlug = request()->brandSlug;

        if ($categorySlug) {
            $categories = $store->categories()->whereSlugTree($categorySlug)->get();
            $categoryIds = $categories->pluck('id')->all();

            if ($categories->where('slug', $categorySlug)->count()) {
                $subtitle = __('Category').': '.$categories->where('slug', $categorySlug)->first()->title;
            }

            $products = $products->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            });
        }

        if ($brandSlug) {
            $products = $products->whereBrandSlug($brandSlug);

            if ($products->count()) {
                $subtitle = __('Brand').': '.$products->first()->brand;
            }
        }

        $products = $products->paginate(30);

        return view('livewire.web.show-catalog')->with([
            'store' => $store,
            'products' => $products,
            'subtitle' => $subtitle,
        ]);
    }
}
