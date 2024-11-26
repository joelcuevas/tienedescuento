<?php

namespace App\Console\Commands\Crawlers;

use App\Jobs\Chascity\FetchProduct;
use Illuminate\Console\Command;

class ChascityCrawler extends Command
{
    protected $signature = 'crawl:chascity';

    protected $description = 'Crawls preciominimo.chascity.com';

    public function handle()
    {
        FetchProduct::dispatch('liverpool', '11606825112');
    }
}
