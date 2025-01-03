<?php

namespace App\Models\Traits\Url;

use App\Jobs\CrawlUrl;
use App\Models\Url;
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
                $url = Url::create([
                    'priority' => $priority,
                    'href' => $href,
                    'domain' => parse_url($href, PHP_URL_HOST),
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
        $this->scheduled_at = now()->addHours($hours);
        $this->delayed_at = now();

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

    public function hit(int $status, int $cooldown, ?float $crawlingTime = null)
    {
        $crawlingTime = $crawlingTime ?? microtime(true);

        // add 2^($streak % 7) days of cooldown if a streak of status >= 300 occurs
        // example: streak(7) = cumulative sum of 2 + 4 + 8 + 16 + 32 + 64 ≈ 4 months
        // after this, $streak % 7 causes the cycle to restart at 2 days again

        $streak = ($status == $this->status) ? $this->streak + 1 : 1;

        if ($status >= 300) {
            $penalty = 2 ** (($streak % 7) - 1);
            $scheduledAt = $penalty;
        } else {
            $scheduledAt = $cooldown;
        }

        $this->streak = $streak;
        $this->status = $status;
        $this->crawled_at = now();
        $this->crawling_time = round(microtime(true) - $crawlingTime, 2);
        $this->scheduled_at = now()->addDays($scheduledAt);
        $this->reserved_at = null;
        $this->delayed_at = null;
        $this->hits = $this->hits + 1;
        $this->save();
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
        return config('crawler.domains')[$this->domain][$key] ?? $default;
    }
}
