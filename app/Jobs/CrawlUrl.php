<?php

namespace App\Jobs;

use App\Jobs\Middleware\ThrottleCrawlerRequests;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CrawlUrl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $url,
    ) {}

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

            if ($crawler->matches()) {
                $crawler->crawl();
            }
        }
    }

    public function middleware(): array
    {
        $domain = parse_url($this->url, PHP_URL_HOST);

        return [new ThrottleCrawlerRequests($domain)];
    }
}
