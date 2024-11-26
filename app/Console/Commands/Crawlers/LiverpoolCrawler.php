<?php

namespace App\Console\Commands\Crawlers;

use App\Jobs\Liverpool\FetchSitemap;
use Illuminate\Console\Command;

class LiverpoolCrawler extends Command
{
    protected $signature = 'crawl:liverpool';

    protected $description = 'Crawls liverpool.com.mx';

    public function handle()
    {
        FetchSitemap::dispatch();
    }
}
