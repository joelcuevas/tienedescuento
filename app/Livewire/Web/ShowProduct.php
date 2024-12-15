<?php

namespace App\Livewire\Web;

use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ShowProduct extends Component
{
    public ?Store $store = null;

    public ?Product $product = null;

    public function mount(string $countryCode, string $storeSlug, string $productSku)
    {
        $this->store = Store::whereCountry($countryCode)->whereSlug($storeSlug)->first();

        if ($this->store) {
            $this->product = Product::whereStoreId($this->store->id)->whereSku($productSku)->first();
        }
    }

    public function render()
    {
        $data = [];

        if ($this->product) {
            $data = collect();

            if (Auth::user()) {
                $data = Price::selectRaw("date_format(priced_at, '%d-%b-%y') as date, min(price) as aggregate")
                    ->where('product_id', $this->product->id)
                    ->groupBy('date')
                    ->groupBy('priced_at')
                    ->orderBy('priced_at')
                    ->get();
            }

            $this->product->increment('views');
            $this->product->store()->increment('views');
            $this->product->categories()->increment('views');
        }

        return view('livewire.web.show-product')->with([
            'data' => $data,
        ]);
    }
}
