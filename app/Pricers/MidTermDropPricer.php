<?php

namespace App\Pricers;

use App\Models\Product;

/**
 * This class determines the price status of a product by analyzing recent and historical prices.
 *
 * Discount finding logic:
 * - Prioritize clusters from the last 15 days to reflect recent trends.
 * - If a stable cluster is found in recent prices, it is treated as the regular price.
 * - If recent prices are unstable, fallback to historical clusters or the latest price.
 * - The discount percentage and savings are calculated relative to the regular price.
 * - Prices are clustered by proximity, and gaps in the price history are filled using the last known price.
 */
class MidTermDropPricer
{
    const DAYS_THRESHOLD = 90; // historical baseline range

    const RECENT_DAYS = 15;   // recent baseline range

    const CLUSTER_THRESHOLD_BASE = 0.2; // base threshold for clustering

    const BAND_MARGIN = 0.05; // margin for regular price bounds

    public function __construct(protected Product $product) {}

    /**
     * Main method to calculate the product's price status, regular price, and discounts.
     * - Fills gaps in the price history.
     * - Clusters recent and historical prices.
     * - Prioritizes recent stable clusters for regular price determination.
     * - Fallbacks to historical clusters or the latest price if no stable clusters are found.
     */
    public function price(): bool
    {
        $prices = $this->product
            ->prices()
            ->where('priced_at', '>=', now()->subDays(self::DAYS_THRESHOLD))
            ->orderBy('priced_at')
            ->get()
            ->toArray();

        if (empty($prices)) {
            return false;
        }

        $prices = $this->fillPriceGaps($prices);

        $latestDate = $prices[count($prices) - 1]['priced_at'];
        $historicalStart = $latestDate->copy()->subDays(self::DAYS_THRESHOLD);
        $recentStart = $latestDate->copy()->subDays(self::RECENT_DAYS);

        $recentPrices = array_filter($prices, fn ($price) => $price['priced_at'] >= $recentStart);
        $historicalPrices = array_filter($prices, fn ($price) => $price['priced_at'] >= $historicalStart && $price['priced_at'] < $recentStart);

        $recentClusters = $this->performClustering(array_column($recentPrices, 'price'), self::CLUSTER_THRESHOLD_BASE);
        $historicalClusters = $this->performClustering(array_column($historicalPrices, 'price'), self::CLUSTER_THRESHOLD_BASE);

        $recentRegularCluster = $this->findMostProbableCluster($recentClusters);

        if (! empty($recentRegularCluster) && $this->isStableCluster($recentRegularCluster)) {
            $regularPrice = array_sum($recentRegularCluster) / count($recentRegularCluster);
        } else {
            $historicalRegularCluster = $this->findMostProbableCluster($historicalClusters);
            if (! empty($historicalRegularCluster)) {
                $regularPrice = array_sum($historicalRegularCluster) / count($historicalRegularCluster);
            } else {
                // Fallback: Use the latest price as the regular price
                $latest = end($prices);
                $regularPrice = $latest['price'];
            }
        }

        // Snap regular price to the closest real price
        $realPrices = array_column($prices, 'price');
        $regularPrice = $this->snapToClosest($regularPrice, $realPrices);

        // Calculate regular price bounds without snapping
        $regularPriceLower = $regularPrice * (1 - self::BAND_MARGIN);
        $regularPriceUpper = $regularPrice * (1 + self::BAND_MARGIN);

        $latest = end($prices);
        $latestPrice = $latest['price'];

        $minimumPrice = min(array_column($prices, 'price'));
        $maximumPrice = max(array_column($prices, 'price'));

        $status = $this->determineStatus($latestPrice, $regularPriceLower, $regularPriceUpper);

        $discount = $savings = 0;

        if ($regularPrice > 0) {
            $discount = (($regularPrice - $latestPrice) / $regularPrice) * 100;
            $savings = $regularPrice - $latestPrice;
        }

        // Ensure all fields are always populated
        $this->product->latest_price = $latestPrice;
        $this->product->priced_at = $latest['priced_at'];
        $this->product->minimum_price = $minimumPrice;
        $this->product->maximum_price = $maximumPrice;
        $this->product->regular_price = $regularPrice;
        $this->product->regular_price_lower = $regularPriceLower;
        $this->product->regular_price_upper = $regularPriceUpper;
        $this->product->discount = $discount;
        $this->product->savings = $savings;
        $this->product->status = $status;
        $this->product->pricer_class = __CLASS__;
        $this->product->pricer_days_history = self::DAYS_THRESHOLD;

        return $this->product->save();
    }

    /**
     * Finds the closest real price to the given value.
     */
    private function snapToClosest(float $value, array $realPrices): float
    {
        if (empty($realPrices)) {
            return $value; // Fallback to original value if no real prices are available
        }

        return collect($realPrices)
            ->sortBy(fn ($price) => abs($price - $value))
            ->first();
    }

    /**
     * Fills gaps in the price history using the last known price.
     */
    private function fillPriceGaps(array $prices): array
    {
        $filledPrices = [];
        $lastPrice = null;

        foreach ($prices as $price) {
            $currentDate = \Carbon\Carbon::parse($price['priced_at']);

            if ($lastPrice !== null) {
                $previousDate = $lastPrice['priced_at']->copy();
                while ($previousDate->addDay()->lessThan($currentDate)) {
                    $filledPrices[] = [
                        'price' => $lastPrice['price'],
                        'priced_at' => $previousDate->copy(),
                    ];
                }
            }

            $filledPrices[] = [
                'price' => $price['price'],
                'priced_at' => $currentDate,
            ];

            $lastPrice = [
                'price' => $price['price'],
                'priced_at' => $currentDate,
            ];
        }

        return $filledPrices;
    }

    /**
     * Groups prices into clusters based on a threshold.
     */
    private function performClustering(array $prices, float $threshold): array
    {
        sort($prices);
        $clusters = [];

        foreach ($prices as $price) {
            $found = false;

            foreach ($clusters as &$cluster) {
                if (abs($price - $cluster[0]) / $cluster[0] <= $threshold) {
                    $cluster[] = $price;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $clusters[] = [$price];
            }
        }

        return $clusters;
    }

    /**
     * Identifies the most probable cluster by size.
     */
    private function findMostProbableCluster(array $clusters): array
    {
        usort($clusters, fn ($a, $b) => count($b) <=> count($a));

        return $clusters[0] ?? [];
    }

    /**
     * Checks if a cluster is stable based on its variance.
     */
    private function isStableCluster(array $cluster): bool
    {
        if (count($cluster) < 3) {
            return false;
        }

        $variance = $this->variance($cluster);

        return $variance <= 0.1 * pow(array_sum($cluster) / count($cluster), 2);
    }

    /**
     * Calculates the variance of a data set.
     */
    private function variance(array $data): float
    {
        if (count($data) <= 1) {
            return 0;
        }

        $mean = array_sum($data) / count($data);
        $squaredDiffs = array_map(fn ($x) => pow($x - $mean, 2), $data);

        return array_sum($squaredDiffs) / count($squaredDiffs);
    }

    /**
     * Determines the product's status based on the latest price.
     */
    private function determineStatus(float $latestPrice, float $regularPriceLower, float $regularPriceUpper): string
    {
        if ($latestPrice < $regularPriceLower) {
            return 'discounted';
        }

        if ($latestPrice > $regularPriceUpper) {
            return 'overpriced';
        }

        return 'regular';
    }
}
