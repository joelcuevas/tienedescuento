<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Support\Str;
use Livewire\Attributes\Url as AttrUrl;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProducts extends Component
{
    use WithPagination;

    #[AttrUrl]
    public string $query = '';

    public string $title;

    public $countryCode;

    public function mount(string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    private function search()
    {
        $storeIds = Store::whereCountry($this->countryCode)->pluck('id')->all();

        // search by url
        if (Str::startsWith($this->query, 'https://')) {
            $byUrl = Url::resolve($this->query);

            // if there's only one result, redirect to the pdp
            if ($byUrl && $byUrl->product) {
                $this->redirect($byUrl->product->link());
            }
        }

        // search by sku
        $bySku = Product::whereIn('store_id', $storeIds)->whereSku($this->query);
        $bySkuCount = $bySku->count();

        // if there's only one result, redirect to the pdp
        if ($bySkuCount == 1) {
            $this->redirect($bySku->first()->link());
        }

        // if there is more than one result, show the grid
        if ($bySkuCount > 1) {
            return $bySku;
        }

        // if no matches, search by terms
        $bySearch = Product::search($this->query);

        return $bySearch;
    }

    public function render()
    {
        $products = $this->search()
            ->orderByDesc('discount')
            ->limitedPaginate()
            ->appends(['query' => $this->query]);

        // load at the undelying collection to not modify the paginator
        $products->getCollection()->load('store');

        return view('livewire.web.show-catalog')->with([
            'title' => $this->query,
            'products' => $products,
        ]);
    }
}
