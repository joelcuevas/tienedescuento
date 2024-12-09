<?php

namespace App\Console\Commands;

use App\Jobs\CrawlUrl as CrawlUrl;
use Illuminate\Console\Command;

class UrlCrawl extends Command
{
    protected $signature = 'url:crawl {url} {--sync}';

    protected $description = 'Crawl a URL using its designated crawling class.';

    public function handle()
    {
        $sync = $this->option('sync');
        $connection = $sync ? 'sync' : config('queue.default');

        CrawlUrl::dispatch($this->argument('url'))->onConnection($connection);
    }
}
