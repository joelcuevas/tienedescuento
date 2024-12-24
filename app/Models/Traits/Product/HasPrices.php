<?php

namespace App\Models\Traits\Product;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasPrices
{
    protected function latestPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->latest_price, 2),
        );
    }

    protected function minumimPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->minumim_price, 2),
        );
    }

    protected function maximumPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->maximum_price, 2),
        );
    }

    protected function regularPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->regular_price, 2),
        );
    }

    protected function regularPriceLowerFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->regular_price_lower, 2),
        );
    }

    protected function regularPriceUpperFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->regular_price_upper, 2),
        );
    }

    public function hasBestPrice(): bool
    {
        return $this->latest_price <= $this->minimum_price;
    }

    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    public function updatePrices(): bool
    {
        $prices = $this->prices()->get();
        $latest = $prices->sortByDesc('priced_at')->first();
        $regularPrice = $this->findRegularPrice();

        $this->latest_price = $latest->price;
        $this->priced_at = $latest->priced_at;
        $this->minimum_price = $prices->min('price');
        $this->maximum_price = $prices->max('price');
        $this->regular_price = $regularPrice['price'];
        $this->regular_price_upper = $regularPrice['upper_band'];
        $this->regular_price_lower = $regularPrice['lower_band'];
        $this->discount = (($this->regular_price - $latest->price) / $this->regular_price) * 100;

        return $this->save();
    }

    /*
    public function findRegularPrice(): array
    {
        $prices = $this->prices->pluck('price')->sort()->all();

        // clusterize the prices within the threshold
        $totalAveragePrice = array_sum($prices) / count($prices);
        $threshold = $totalAveragePrice * 0.10;
        $clusters = [];

        foreach ($prices as $price) {
            $clusterKey = floor($price / $threshold);
            $clusters[$clusterKey][] = $price;
        }

        // find the average price for the largest cluster
        $largestCluster = collect($clusters)->sortByDesc(fn ($c) => count($c))->first();
        $averagePrice = array_sum($largestCluster) / count($largestCluster);

        // find the max/min bands within two standard deviations
        $pows = array_map(fn ($price) => pow($price - $averagePrice, 2), $largestCluster);
        $stdDeviation = sqrt(array_sum($pows) / count($largestCluster));
        $minBandWidth = $averagePrice * 0.05;
        $lowerBand = min($averagePrice - (2 * $stdDeviation), $averagePrice - $minBandWidth);
        $upperBand = max($averagePrice + (2 * $stdDeviation), $averagePrice + $minBandWidth);

        // find the closest-to-the-average real price
        $closestPrice = collect($largestCluster)->sortBy(fn ($p) => abs($p - $averagePrice))->first();

        return [
            'price' => $closestPrice,
            'lower_band' => $lowerBand,
            'upper_band' => $upperBand,
        ];
    }
    */

    public function findRegularPrice(): array
    {
        // Retrieve and sort prices
        $prices = collect($this->prices->pluck('price'))->sort()->values()->all();

        // Define number of bins (adjustable)
        $binCount = 10;

        // Calculate bin width
        $minPrice = min($prices);
        $maxPrice = max($prices);
        $range = $maxPrice - $minPrice;

        // Handle case where all prices are identical
        if ($range == 0) {
            return [
                'price' => $minPrice,
                'lower_band' => $minPrice,
                'upper_band' => $minPrice,
            ];
        }

        $binWidth = $range / $binCount;

        // Create bins and calculate frequency
        $bins = array_fill(0, $binCount, 0);
        foreach ($prices as $price) {
            $binIndex = (int) floor(($price - $minPrice) / $binWidth);
            if ($binIndex >= $binCount) {
                $binIndex = $binCount - 1; // Ensure it falls within range
            }
            $bins[$binIndex]++;
        }

        // Find the bin with the highest frequency
        $maxBinIndex = array_keys($bins, max($bins))[0];
        $lowerBand = $minPrice + $maxBinIndex * $binWidth;
        $upperBand = $lowerBand + $binWidth;

        // Filter prices within the most frequent bin
        $pricesInBin = array_filter($prices, function ($price) use ($lowerBand, $upperBand) {
            return $price >= $lowerBand && $price < $upperBand;
        });

        // Find the closest price to the bin center
        $binCenter = ($lowerBand + $upperBand) / 2;
        $closestPrice = collect($pricesInBin)->sortBy(function ($price) use ($binCenter) {
            return abs($price - $binCenter);
        })->first();

        // Return results
        return [
            'price' => $closestPrice,
            'lower_band' => $lowerBand,
            'upper_band' => $upperBand,
        ];
    }
}
