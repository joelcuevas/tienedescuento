<?php

namespace App\Models\Traits\Url;

use App\Jobs\CrawlUrl;
use App\Models\Store;
use App\Models\Url;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

trait HasCrawlers
{
    public static function resolve(string $href, int $priority = 99): ?Url
    {
        // first check if this url is an alias
        $alias = self::resolveAlias($href);
        $href = $alias ?? $href;

        // then look for a direct hash match
        $hash = sha1($href);
        $url = Url::whereHash($hash)->first();

        if ($url == null) {
            // if not, find a matching crawler
            $crawler = self::resolveCrawler($href);

            if ($crawler) {
                // look for an existing product
                $product = $crawler::matchesProduct($href);

                // is there an existing and equivalent url?
                if ($product && $product->url) {
                    return $product->url;
                }

                // if there's not, create a new one
                $domain = parse_url($href, PHP_URL_HOST);

                // find the store this domain belongs to
                $matchedCountry = null;
                $matchedSlug = null;

                foreach (config('stores') as $countryCode => $stores) {
                    foreach ($stores as $storeSlug => $store) {
                        if ($store['domain'] === $domain) {
                            $matchedCountry = $countryCode;
                            $matchedSlug = $storeSlug;

                            break 2;
                        }
                    }
                }

                $store = Store::whereCountry($matchedCountry)->whereSlug($matchedSlug)->first();

                $url = Url::create([
                    'store_id' => $store?->id ?? null,
                    'priority' => $priority,
                    'href' => $href,
                    'domain' => $domain,
                    'scheduled_at' => now()->subMinutes(1),
                    'hash' => $hash,
                    'crawler_class' => $crawler,
                ]);

                // match the existing product to this url
                if ($product) {
                    $product->url_id = $url->id;
                    $product->save();
                }
            }
        }

        return $url;
    }

    public function scheduleNow(): bool
    {
        $this->scheduled_at = now();

        return $this->save();
    }

    public function delay(int $hours = 48): bool
    {
        $status = Response::HTTP_RESET_CONTENT;
        $this->scheduled_at = now()->addHours($hours);
        $this->delayed_at = now();
        $this->status = $status;

        return $this->save();
    }

    public function dispatch(bool $sync = false): void
    {
        $this->reserved_at = now();
        $this->save();

        $connection = $sync ? 'sync' : config('queue.default');
        $queue = $this->getCrawlerConfig('queue', 'default');

        CrawlUrl::dispatch($this)->onConnection($connection)->onQueue($queue);
    }

    public function release()
    {
        $this->reserved_at = null;

        return $this->save();
    }

    public function crawl(): void
    {
        (new $this->crawler_class($this))->crawl();
    }

    public function hit(int $status, int $cooldown, ?float $crawlingTime = null, int $crawledProducts = 0, int $discoveredProducts = 0)
    {
        $crawlingTime = $crawlingTime ?? microtime(true);

        // add 2^($streak % 7) days of cooldown if a streak of status >= 300 occurs
        // example: streak(7) = cumulative sum of 2 + 4 + 8 + 16 + 32 + 64 ≈ 4 months
        // after this, $streak % 7 causes the cycle to restart at 2 days again

        $streak = ($status == $this->status) ? $this->streak + 1 : 1;

        if ($status == Response::HTTP_OK) {
            $scheduledAt = $cooldown;
        } else if ($status == Response::HTTP_IM_USED) {
            $scheduledAt = 365 * 10;
            $this->is_active = false;
        } else {
            $penalty = 2 ** (($streak % 7) - 1);
            $scheduledAt = $penalty;
        }

        $this->streak = $streak;
        $this->status = $status;
        $this->crawled_products = $crawledProducts;
        $this->discovered_products = $discoveredProducts;
        $this->crawled_at = now();
        $this->crawling_time = round(microtime(true) - $crawlingTime, 2);
        $this->scheduled_at = now()->addDays($scheduledAt);
        $this->reserved_at = null;
        $this->delayed_at = null;
        $this->hits = $this->hits + 1;
        $this->save();

        if ($this->product) {
            $isActive = $this->status >= 200 && $this->status <= 299;
            $this->product->is_active = $isActive;
            $this->product->save();
        }
    }

    private static function resolveCrawler($href): ?string
    {
        // get all crawler classes, ignoring base crawlers
        $files = File::allFiles(app_path('Crawlers'));

        $crawlers = array_filter($files, function ($file) {
            $name = $file->getFilename();

            return str_ends_with($name, 'Crawler.php') && ! str_ends_with($name, 'BaseCrawler.php');
        });

        // evaluate which one matches the url href
        foreach ($crawlers as $file) {
            $path = str_replace(app_path(), 'App', $file->getPathname());
            $crawler = '\\'.str_replace('/', '\\', rtrim($path, '.php'));

            if ($crawler::matchesPattern($href)) {
                return $crawler;
            }
        }

        return null;
    }

    private static function resolveAlias($href): ?string
    {
        $files = File::allFiles(app_path('Crawlers'));

        $aliases = array_filter($files, function ($file) {
            return str_ends_with($file->getFilename(), 'Alias.php');
        });

        foreach ($aliases as $file) {
            $path = str_replace(app_path(), 'App', $file->getPathname());
            $alias = '\\'.str_replace('/', '\\', rtrim($path, '.php'));

            if ($match = $alias::matchesPattern($href)) {
                return $match;
            }
        }

        return null;
    }

    public function getCrawlerConfig(string $key, mixed $default = null): mixed
    {
        return config('crawlers.domains')[$this->domain][$key] ?? $default;
    }
}
