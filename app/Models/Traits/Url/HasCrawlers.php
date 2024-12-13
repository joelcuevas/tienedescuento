<?php

namespace App\Models\Traits\Url;

use App\Jobs\CrawlUrl;
use App\Models\Url;
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

    public function dispatch(bool $sync = false): void
    {
        $this->reserved_at = now();
        $this->save();

        $connection = $sync ? 'sync' : config('queue.default');
        $queue = $this->getCrawlerConfig('queue', 'default');

        CrawlUrl::dispatch($this)->onConnection($connection)->onQueue($queue);
    }

    public function crawl(): void
    {
        (new $this->crawler_class($this))->crawl();
    }
}
