<?php

namespace App\Livewire\Web;

use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
use Livewire\Component;
use Sentry;

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

        Sentry\configureScope(function (Sentry\State\Scope $scope): void {
            if ($this->product) {
                $scope->setContext('Product', [
                    'id' => $this->product->id,
                ]);
            }

            if ($this->store) {
                $scope->setTag('store', $this->store->slug);
            }
        });
    }

    public function render()
    {
        $data = [];
        $related = null;

        $lastMonths = 2;
        $ago = now()->subMonths($lastMonths)->format('Y');
        $now = now()->format('Y');
        $yearsSpan = $ago == $now ? $now : $ago.'-'.$now;

        if ($this->product) {
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
                ->take(10)
                ->get()
                ->load('store')
                ->except($this->product->id)
                ->take(6);
        }

        return view('livewire.web.show-product')->with([
            'data' => $data,
            'lastMonths' => $lastMonths,
            'yearsSpan' => $yearsSpan,
            'related' => $related,
        ]);
    }
}
