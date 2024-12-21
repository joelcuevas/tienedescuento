<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProduct extends Component
{
    use WithPagination;

    #[Url]
    public string $q;

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
            $byUrl = Product::searchByUrl($this->q);

            if ($byUrl->count() == 1) {
                // if there's only one result, redirect to the pdp
                $this->redirect($byUrl->first()->link);
            }
        }

        // search by sku
        $bySku = Product::whereIn('store_id', $storeIds)->whereSku($this->q);

        if ($bySku->count() == 1) {
            // if there's only one result, redirect to the pdp
            $this->redirect($bySku->first()->link);
        }

        if ($bySku->count() > 1) {
            // if there is more than one result, show the grid
            return $bySku;
        }

        // if no matches, search by terms
        $bySearch = Product::search($this->q);

        return $bySearch;
    }

    public function render()
    {
        return view('livewire.web.search-product')->with([
            'products' => $this->search()->orderByDesc('discount')->paginate(30),
        ]);
    }
}
