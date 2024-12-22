<?php

namespace App\Livewire\Web;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Support\LimitedPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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
            $categories = Category::whereSlugTree($categorySlug)->get();
            $categoryIds = $categories->pluck('id')->all();

            $insertValues = implode(',', array_map(fn($id) => "($id)", $categoryIds));
            DB::statement('CREATE TEMPORARY TABLE temp_category_ids (id INT PRIMARY KEY)');
            DB::statement("INSERT INTO temp_category_ids (id) VALUES $insertValues");

            $query
                ->select('products.*')
                ->join(
                    DB::raw('category_product FORCE INDEX (category_product_product_id_category_id_index)'), 
                    'products.id', '=', 'category_product.product_id',
                )
                ->join('temp_category_ids', 'category_product.category_id', '=', 'temp_category_ids.id');

            if ($categories->where('slug', $categorySlug)->count()) {
                $this->title[] = $categories->where('slug', $categorySlug)->first()->title;
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
            'products' => LimitedPaginator::fromQuery($query, 36, 360),
        ]);
    }
}
