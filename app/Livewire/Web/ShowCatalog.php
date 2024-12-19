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

        if (request()->categorySlug) {
            $categories = $store->categories()->whereSlug(request()->categorySlug)->get();
            $categoryIds = $categories->pluck('id')->all();
            $subtitle = __('Category').': '.$categories->first()->title;

            $products = $products->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            });
        }

        if (request()->brandSlug) {
            $products = $products->whereBrandSlug(request()->brandSlug);
            $subtitle = __('Brand').': '.$products->first()->brand;
        }

        $products = $products->paginate(30);

        return view('livewire.web.show-catalog')->with([
            'store' => $store,
            'products' => $products,
            'subtitle' => $subtitle,
        ]);
    }
}
