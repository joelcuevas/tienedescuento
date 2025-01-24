<?php

namespace App\Pricers;

use App\Models\Product;
use Carbon\Carbon;

/**
 * This class determines the price status of a product by analyzing recent and historical prices.
 *
 * Discount-finding strategy:
 * - Prioritizes recent price trends to identify the most probable "regular" price range.
 * - Recent prices (last 30 days) are analyzed to form clusters based on proximity.
 * - If a stable cluster is found in the recent prices, it is used as the regular price.
 * - If recent prices are unstable, the algorithm falls back to historical data (last 90 days) to identify regular prices.
 * - Historical data is strictly limited to the last 90 days to avoid inflating clusters from irrelevant older data.
 * - Gaps in the price history are filled using the last known price but only starting from the first known price within the 90-day range.
 * - Once a "regular" price is determined:
 *     - It is snapped to the closest actual price to reflect realistic values.
 *     - Upper and lower bounds are calculated using a margin (e.g., ±5%) around the regular price.
 * - The status of the latest price is then determined as one of:
 *     - "discounted" (below the lower bound),
 *     - "overpriced" (above the upper bound), or
 *     - "regular" (within the bounds).
 * - Discounts and savings are computed relative to the regular price.
 */
class MidTermDropPricer
{
    const VERSION = '1.0.2';

    const DAYS_THRESHOLD = 90; // historical baseline range

    const RECENT_DAYS = 30;    // recent baseline range

    const CLUSTER_THRESHOLD_BASE = 0.1; // base threshold for clustering

    const BAND_MARGIN = 0.05;  // margin for regular price bounds

    public function __construct(protected Product $product) {}

    /**
     * Calculates and updates the product's price status.
     *
     * @return bool True if the product was successfully updated, false otherwise.
     */
    public function price(): bool
    {
        // fetch prices within the last 90 days
        $prices = $this->product
            ->prices()
            ->where('priced_at', '>=', now()->subDays(self::DAYS_THRESHOLD))
            ->orderBy('priced_at')
            ->get()
            ->toArray();

        // exit early if no price data is available
        if (empty($prices)) {
            return false;
        }

        // fill missing dates with the last known price
        $prices = $this->fillPriceGaps($prices);

        // determine date ranges for historical and recent prices
        $latestDate = Carbon::parse($prices[count($prices) - 1]['priced_at']);
        $historicalStart = $latestDate->copy()->subDays(self::DAYS_THRESHOLD);
        $recentStart = $latestDate->copy()->subDays(self::RECENT_DAYS);

        // separate recent prices from historical prices based on defined ranges
        $recentPrices = array_filter($prices, fn ($price) => $price['priced_at'] >= $recentStart);
        $historicalPrices = array_filter($prices, fn ($price) => $price['priced_at'] >= $historicalStart && $price['priced_at'] < $recentStart);

        // perform clustering on recent and historical prices
        $recentClusters = $this->performClustering(array_column($recentPrices, 'price'), self::CLUSTER_THRESHOLD_BASE);
        $historicalClusters = $this->performClustering(array_column($historicalPrices, 'price'), self::CLUSTER_THRESHOLD_BASE);

        // prioritize recent stable clusters for determining the regular price
        $recentRegularCluster = $this->findMostProbableCluster($recentClusters);

        if (! empty($recentRegularCluster) && $this->isStableCluster($recentRegularCluster)) {
            // use the mean of the most stable recent cluster
            $regularPrice = array_sum($recentRegularCluster) / count($recentRegularCluster);
        } else {
            // fallback to historical clusters if recent clusters are unstable
            $historicalRegularCluster = $this->findMostProbableCluster($historicalClusters);

            if (! empty($historicalRegularCluster)) {
                // use the mean of the largest historical cluster
                $regularPrice = array_sum($historicalRegularCluster) / count($historicalRegularCluster);
            } else {
                // use the latest price if no stable cluster is found
                $latest = end($prices);
                $regularPrice = $latest['price'];
            }
        }

        // snap the calculated regular price to the closest actual price
        $regularPrice = $this->snapToClosest($regularPrice, array_column($prices, 'price'));

        // calculate the upper and lower bounds of the regular price range
        $regularPriceLower = $regularPrice * (1 - self::BAND_MARGIN);
        $regularPriceUpper = $regularPrice * (1 + self::BAND_MARGIN);

        $latest = end($prices);
        $latestPrice = $latest['price'];

        // compute the minimum and maximum prices in the dataset
        $minimumPrice = min(array_column($prices, 'price'));
        $maximumPrice = max(array_column($prices, 'price'));

        // determine the price status based on the regular price range
        $status = $this->determineStatus($latestPrice, $regularPriceLower, $regularPriceUpper);

        $discount = $savings = 0;

        if ($regularPrice > 0) {
            // calculate the discount percentage and savings
            $discount = (($regularPrice - $latestPrice) / $regularPrice) * 100;
            $savings = $regularPrice - $latestPrice;
        }

        // populate the product with calculated fields
        $this->product->is_active = $latest['priced_at']->isAfter(now()->subDays(Product::DAYS_OUTDATED));
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
        $this->product->pricer_class = class_basename(__CLASS__).'@'.self::VERSION;
        $this->product->pricer_days_history = self::DAYS_THRESHOLD;

        return $this->product->save();
    }

    /**
     * Fills missing dates in the price history using the last known price.
     */
    private function fillPriceGaps(array $prices): array
    {
        $filledPrices = [];
        $lastPrice = null;

        // only start filling gaps from the first known price
        $firstKnownDate = Carbon::parse($prices[0]['priced_at']);

        foreach ($prices as $price) {
            $currentDate = Carbon::parse($price['priced_at']);

            if ($lastPrice !== null) {
                $previousDate = $lastPrice['priced_at']->copy();

                // fill gaps between consecutive dates with the last known price
                while ($previousDate->addDay()->lessThan($currentDate) && $previousDate->greaterThanOrEqualTo($firstKnownDate)) {
                    $filledPrices[] = [
                        'price' => $lastPrice['price'],
                        'priced_at' => $previousDate->copy(),
                    ];
                }
            }

            // add the current price to the filled list
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
     * Groups prices into clusters based on proximity.
     */
    private function performClustering(array $prices, float $threshold): array
    {
        // sort prices to ensure consistent clustering
        sort($prices);
        $clusters = [];

        foreach ($prices as $price) {
            $found = false;

            foreach ($clusters as &$cluster) {
                // check if the price fits within the threshold of an existing cluster
                if (abs($price - $cluster[0]) / $cluster[0] <= $threshold) {
                    $cluster[] = $price;
                    $found = true;
                    break;
                }
            }

            // create a new cluster if no match is found
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
        // sort clusters by size, largest first
        usort($clusters, fn ($a, $b) => count($b) <=> count($a));

        return $clusters[0] ?? [];
    }

    /**
     * Snaps a calculated price to the closest actual price in the dataset.
     */
    private function snapToClosest(float $value, array $realPrices): float
    {
        if (empty($realPrices)) {
            return $value;
        }

        // find the closest price in the dataset
        return collect($realPrices)
            ->sortBy(fn ($price) => abs($price - $value))
            ->first();
    }

    /**
     * Determines whether a cluster is stable based on its variance.
     */
    private function isStableCluster(array $cluster): bool
    {
        if (count($cluster) < 3) {
            return false;
        }

        // compute variance of the cluster
        $variance = $this->variance($cluster);

        // compare variance to an acceptable threshold
        return $variance <= 0.1 * pow(array_sum($cluster) / count($cluster), 2);
    }

    /**
     * Calculates the variance of a dataset.
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
     * Determines the price status (discounted, regular, or overpriced).
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
