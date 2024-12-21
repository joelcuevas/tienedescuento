<?php

namespace App\Console\Commands;

use App\Models\Url;
use Illuminate\Console\Command;

class UrlCrawl extends Command
{
    protected $signature = 'url:crawl {url} {--sync}';

    protected $description = 'Crawl a URL using its designated crawling class.';

    public function handle()
    {
        $url = Url::resolve($this->argument('url'));

        if ($url) {
            $this->line("[URL {$url->id}] {$url->href}");
            $url->dispatch($this->option('sync'));
        }
    }
}
