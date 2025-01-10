<?php

namespace App\Livewire\Web;

use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
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
        $related = null;

        $lastMonths = 6;
        $ago = now()->subMonths($lastMonths)->format('Y');
        $now = now()->format('Y');
        $yearsSpan = $ago == $now ? $now : $ago.'-'.$now;

        if ($this->product) {
            $data = collect();

            $data = Price::selectRaw("date_format(priced_date, '%d-%b-%y') as date, priced_date, min(price) as aggregate")
                ->where('product_id', $this->product->id)
                ->where('priced_date', '>=', now()->subMonths($lastMonths)->startOfMonth())
                ->groupBy('date')
                ->groupBy('priced_date')
                ->orderBy('priced_date')
                ->get();

            $this->product->increment('views');
            $this->product->store()->increment('views');
            $this->product->categories()->increment('views');

            $related = Product::search($this->product->title)
                ->take(6)
                ->get()
                ->except($this->product->id);
        }

        return view('livewire.web.show-product')->with([
            'data' => $data,
            'lastMonths' => $lastMonths,
            'yearsSpan' => $yearsSpan,
            'related' => $related,
        ]);
    }
}
