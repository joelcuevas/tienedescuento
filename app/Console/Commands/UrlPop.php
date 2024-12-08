<?php

namespace App\Console\Commands;

use App\Jobs\CrawlUrl;
use App\Models\Url;
use Illuminate\Console\Command;

class UrlPop extends Command
{
    protected $signature = 'url:pop {--limit=50}';

    protected $description = 'Retrieve scheduled URLs from the database for crawling.';

    public function handle()
    {
        $limit = $this->option('limit');
        $urls = Url::scheduled($limit)->get();

        foreach ($urls as $url) {
            $url->reserve();
            CrawlUrl::dispatch($url->href)->onQueue($url->queue());
        }
    }
}
