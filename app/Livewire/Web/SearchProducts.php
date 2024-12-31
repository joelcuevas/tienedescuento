<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\Store;
use App\Models\Url;
use App\Support\LimitedPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Url as AttrUrl;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProducts extends Component
{
    use WithPagination;

    #[AttrUrl]
    public string $q = '';

    public $countryCode;

    public function mount(string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    private function search()
    {
        $storeIds = Store::whereCountry($this->countryCode)->pluck('id')->all();

        // search by url
        if (Str::startsWith($this->q, 'https://')) {
            $byUrl = Url::resolve($this->q);

            // if there's only one result, redirect to the pdp
            if ($byUrl && $byUrl->product) {
                $this->redirect($byUrl->product->link());
            }
        }

        // search by sku
        $bySku = Product::whereIn('store_id', $storeIds)->whereSku($this->q);
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
        $bySearch = Product::search($this->q);

        return $bySearch;
    }

    public function render()
    {
        $query = $this->search()->orderByDesc('discount');

        return view('livewire.web.search-products')->with([
            'products' => $query->paginate(36)->load('store'),
        ]);
    }
}
