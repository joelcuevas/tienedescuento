<?php

namespace App\Models\Traits\Url;

use App\Jobs\CrawlUrl;
use App\Models\Url;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

trait HasCrawlers
{
    public static function resolve(string $href): ?Url
    {
        $hash = sha1($href);
        $url = Url::whereHash($hash)->first();

        if ($url == null) {
            $crawler = self::findCrawlerFqcn($href);

            if ($crawler) {
                $url = Url::create([
                    'href' => $href,
                    'domain' => parse_url($href, PHP_URL_HOST),
                    'scheduled_at' => now()->subMinutes(1),
                    'hash' => $hash,
                    'crawler_class' => $crawler,
                ]);
            }
        }

        return $url;
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

    public function hit(int $status, int $cooldown, float $crawlingTime = null)
    {
        $crawlingTime = $crawlingTime ?? microtime(true);

        // add 2^($streak % 7) days of cooldown if a streak of status >= 300 occurs
        // example: streak(7) = cumulative sum of 2 + 4 + 8 + 16 + 32 + 64 ≈ 4 months
        // after this, $streak % 7 causes the cycle to restart at 2 days again

        $streak = ($status == $this->status) ? $this->streak + 1 : 1;

        if ($status >= 300) {
            $penalty = 2 ** ($streak % 7);
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
        $this->hits = $this->hits + 1;
        $this->save();
    }

    private static function findCrawlerFqcn($href): ?string
    {
        // get all crawler classes, ignoring base crawlers
        $files = collect(File::allFiles(app_path('Crawlers')))
            ->filter(function ($file) { 
                return Str::endsWith($file->getFilename(), 'Crawler.php') && 
                    ! Str::endsWith($file->getFilename(), 'BaseCrawler.php');
            });

        foreach ($files as $file) {
            $relativePath = str_replace(app_path(), 'App', $file->getPathname());
            $fqcn = '\\' . str_replace('/', '\\', rtrim($relativePath, '.php'));

            if ($fqcn::matchesPattern($href)) {
                return $fqcn;
            }
        }

        return null;
    }

    public function getCrawlerConfig(string $key, mixed $default = null): mixed
    {
        return config('crawler.domains')[$this->domain][$key] ?? $default;
    }
}
