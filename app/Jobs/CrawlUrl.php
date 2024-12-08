<?php

namespace App\Jobs;

use App\Jobs\Middleware\ThrottleCrawlers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CrawlUrl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $url,
    ) {}

    public function uniqueId(): string
    {
        return sha1($this->url);
    }

    public function handle(): void
    {
        $files = glob(app_path('Crawlers/*Crawler.php'));

        foreach ($files as $file) {
            $className = basename($file, '.php');

            if (Str::endsWith($className, 'BaseCrawler')) {
                continue;
            }

            $fqcn = "App\\Crawlers\\$className";
            $crawler = new $fqcn($this->url);

            if ($crawler->matchesPattern()) {
                $crawler->crawl();
            }
        }
    }

    public function middleware(): array
    {
        $throttled = new ThrottleCrawlers(parse_url($this->url, PHP_URL_HOST));

        return [$throttled];
    }
}
