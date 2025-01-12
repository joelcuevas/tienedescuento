<?php

namespace App\Pricers;

use App\Models\Product;
use Illuminate\Support\Collection;

class MidTermDropPricer
{
    const MAX_HISTORY_DAYS = 60;
    const RECENT_DAYS = 15;
    const CLUSTER_THRESHOLD = 0.1; // 10% range for clustering

    public function __construct(
        protected Product $product,
    ) {}

    public function price(): bool
    {
        // Fetch prices for the product within the last 60 days
        $prices = $this->product
            ->prices()
            ->where('priced_at', '>=', now()->subDays(self::MAX_HISTORY_DAYS))
            ->orderByDesc('priced_at')
            ->get();

        // Ensure there are prices to evaluate
        if ($prices->isEmpty()) {
            return false;
        }

        // Get the latest price
        $latest = $prices->first();
        $latestPrice = $latest->price;

        // Perform clustering on prices
        $clusters = $this->performClustering($prices->pluck('price')->toArray());

        // Identify the cluster with the highest count (most probable price range)
        $regularCluster = $this->findMostProbableCluster($clusters);
        $regularPrice = array_sum($regularCluster) / count($regularCluster);

        // Calculate upper and lower bounds of the regular price cluster
        $regularPriceLower = min($regularCluster);
        $regularPriceUpper = max($regularCluster);

        // Get the minimum and maximum prices in the last 60 days
        $minimumPrice = $prices->min('price');
        $maximumPrice = $prices->max('price');

        // Get the minimum price in the last 15 days
        $recentPrices = $prices->filter(fn($price) => $price->priced_at >= now()->subDays(self::RECENT_DAYS));
        $recentMinPrice = $recentPrices->min('price');

        // Determine the status of the latest price
        $status = $this->determineStatus($latestPrice, $regularPrice, $regularPriceLower, $regularPriceUpper, $recentMinPrice);

        // Calculate discount and savings
        $discount = 0;
        $savings = 0;

        if ($regularPrice > 0) {
            $discount = (($regularPrice - $latestPrice) / $regularPrice) * 100;
            $savings = $regularPrice - $latestPrice;
        }

        // Update the product with calculated pricing details
        $this->product->latest_price = $latestPrice;
        $this->product->priced_at = $latest->priced_at;
        $this->product->minimum_price = $minimumPrice;
        $this->product->maximum_price = $maximumPrice;
        $this->product->regular_price = $regularPrice;
        $this->product->regular_price_lower = $regularPriceLower;
        $this->product->regular_price_upper = $regularPriceUpper;
        $this->product->discount = $discount;
        // $this->product->savings = $savings;
        // $this->product->status = $status;

        return $this->product->save();
    }

    /**
     * Perform clustering on the given prices.
     *
     * @param array $prices
     * @return array
     */
    private function performClustering(array $prices): array
    {
        sort($prices); // Sort prices for clustering
        $clusters = [];

        foreach ($prices as $price) {
            $found = false;

            foreach ($clusters as &$cluster) {
                // Check if the price fits within the cluster threshold
                if (abs($price - $cluster[0]) / $cluster[0] <= self::CLUSTER_THRESHOLD) {
                    $cluster[] = $price;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $clusters[] = [$price];
            }
        }

        return $clusters;
    }

    /**
     * Find the most probable cluster (the one with the highest count).
     *
     * @param array $clusters
     * @return array
     */
    private function findMostProbableCluster(array $clusters): array
    {
        usort($clusters, fn($a, $b) => count($b) <=> count($a));
        return $clusters[0];
    }

    /**
     * Determine the status of the latest price.
     *
     * @param float $latestPrice
     * @param float $regularPrice
     * @param float $regularPriceLower
     * @param float $regularPriceUpper
     * @param float $recentMinPrice
     * @return string
     */
    private function determineStatus(float $latestPrice, float $regularPrice, float $regularPriceLower, float $regularPriceUpper, float $recentMinPrice): string
    {
        if ($latestPrice < $recentMinPrice && $latestPrice < $regularPrice) {
            return 'discounted';
        }

        if ($latestPrice < $recentMinPrice) {
            return 'dropped';
        }

        if ($regularPriceLower <= $latestPrice && $latestPrice <= $regularPriceUpper) {
            return 'regular';
        }

        return 'overpriced';
    }
}