<?php

namespace App\Console\Commands;

use App\Jobs\CrawlUrl;
use App\Models\Url;
use Illuminate\Console\Command;

class UrlPop extends Command
{
    protected $signature = 'url:pop {--limit=100}';

    protected $description = 'Retrieve scheduled URLs from the database for crawling.';

    public function handle()
    {
        $limit = $this->option('limit');
        $urls = Url::scheduled($limit)->get();

        foreach ($urls as $url) {
            CrawlUrl::dispatch($url->href);
        }
    }
}
