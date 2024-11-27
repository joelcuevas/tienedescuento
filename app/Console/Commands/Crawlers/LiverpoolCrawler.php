<?php

namespace App\Console\Commands\Crawlers;

use App\Jobs\Liverpool\FetchCategory;
use App\Jobs\Liverpool\FetchSitemap;
use Illuminate\Console\Command;

class LiverpoolCrawler extends Command
{
    protected $signature = 'crawl:liverpool {--all} {--cat=}';

    protected $description = 'Crawls liverpool.com.mx';

    public function handle()
    {
        if ($this->option('all')) {
            FetchSitemap::dispatch();
        } elseif ($this->option('cat')) {
            $category = $this->option('cat');
            $url = 'https://www.liverpool.com.mx/tienda/'.$category;
            FetchCategory::dispatch($url);
        }
    }
}
