<?php

namespace App\Console\Commands;

use App\Jobs\CrawlUrl as JobsCrawlUrl;
use Illuminate\Console\Command;

class UrlCrawl extends Command
{
    protected $signature = 'url:crawl {url}';

    protected $description = 'Crawl a URL using its designated crawling class.';

    public function handle()
    {
        JobsCrawlUrl::dispatch($this->argument('url'));
    }
}
