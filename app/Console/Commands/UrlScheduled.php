<?php

namespace App\Console\Commands;

use App\Models\Url;
use Illuminate\Console\Command;

class UrlScheduled extends Command
{
    protected $signature = 'url:scheduled {--limit=50}';

    protected $description = 'Retrieve scheduled URLs from the database for crawling.';

    public function handle()
    {
        $urls = Url::scheduled($this->option('limit'))->get();

        foreach ($urls as $url) {
            $url->dispatch();
        }
    }
}
