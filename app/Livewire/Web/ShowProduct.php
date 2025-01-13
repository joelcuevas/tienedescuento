<?php

namespace App\Livewire\Web;

use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
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
                Debugbar::addMessage('Product ID: '.$this->product->id);

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

        $lastDays = $this->product ? 90 : ($this->product->pricer_days_history ?? 90);
        $ago = now()->subDays($lastDays)->format('Y');
        $now = now()->format('Y');
        $yearsSpan = $ago == $now ? $now : $ago.'-'.$now;

        if ($this->product) {
            $data = $this->getChartData($lastDays);

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
            'lastDays' => $lastDays,
            'yearsSpan' => $yearsSpan,
            'related' => $related,
        ]);
    }

    private function getChartData($lastDays)
    {
        // Define the start and end dates
        $startDate = now()->subDays($lastDays)->startOfDay();
        $endDate = now()->startOfDay();

        // Fetch the original data
        $data = Price::selectRaw("date_format(priced_date, '%d-%b-%y') as date, priced_date, min(price) as aggregate")
            ->where('product_id', $this->product->id)
            ->where('priced_date', '>=', $startDate)
            ->groupBy('date')
            ->groupBy('priced_date')
            ->orderBy('priced_date')
            ->get();

        // Adjust startDate to the first known price date
        $firstKnownDate = Carbon::parse($data->first()->priced_date)->startOfDay();
        $startDate = $firstKnownDate;

        // Fill gaps in dates with the last known price
        $filledData = [];
        $lastKnownPrice = null;

        // Create a map of priced_date to price for quick lookup
        $datePriceMap = $data->pluck('aggregate', 'priced_date')->toArray();

        // Iterate through all dates in the range starting at the first known price
        for ($date = $startDate; $date->lessThanOrEqualTo($endDate); $date->addDay()) {
            $formattedDate = $date->toDateTimeString();

            if (array_key_exists($formattedDate, $datePriceMap)) {
                // Use the price from the data if available
                $lastKnownPrice = $datePriceMap[$formattedDate];
            }

            if ($lastKnownPrice !== null) {
                // Fill the date with the last known price
                $filledData[] = (object) [
                    'date' => $date->format('d-M-y'),
                    'priced_date' => $formattedDate,
                    'aggregate' => $lastKnownPrice,
                ];
            }
        }

        return collect($filledData);
    }
}
