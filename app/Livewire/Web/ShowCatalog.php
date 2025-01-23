<?php

namespace App\Livewire\Web;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Taxonomy;
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
            ->whereIn('id', function ($subquery) {
                $subquery->selectRaw('max(discount)')->from('products')->groupBy('sku');
            })
            ->where('is_active', true)
            ->with(['store'])
            ->orderByDesc('discount')
            ->orderByDesc('priced_at')
            ->limit(360);

        $catalogSlug = request()->catalogSlug;
        $categorySlug = request()->categorySlug;
        $brandSlug = request()->brandSlug;
        $taxonomySlug = request()->taxonomySlug;

        // if there is a store slug, show the store catalog
        $store = Store::whereCountry($countryCode)->whereSlug($catalogSlug)->first();

        if ($store) {
            $query->whereStoreId($store->id);
        } else {
            // if no store is found, asume it's a taxonomy
            $taxonomySlug = $catalogSlug;
        }

        if ($taxonomySlug) {
            $taxonomy = Taxonomy::whereCountry($countryCode)->whereSlug($taxonomySlug)->first();
            abort_unless($taxonomy, 404);

            $query->whereTaxonomySlug($taxonomySlug);

            if ($taxonomy) {
                $this->title[] = $taxonomy->title;
            }
        } else {
            if ($categorySlug) {
                $query->whereCategorySlug($categorySlug);
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
        }

        return view('livewire.web.show-catalog')->with([
            'store' => $store,
            'title' => implode(' / ', array_unique($this->title)),
            'products' => $query->cursorPaginate(Product::PAGE_SIZE),
        ]);
    }
}
