<?php

namespace App\Console\Commands;

use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class UrlScheduled extends Command
{
    protected $signature = 'url:scheduled {--limit=50} {--domain=*}';

    protected $description = 'Retrieve scheduled URLs from the database for crawling.';

    public function handle()
    {
        $urls = Url::scheduled($this->option('limit'));

        if ($this->option('domain')) {
            $urls->where(function (Builder $query) {
                foreach ($this->option('domain') as $domain) {
                    $query->orWhere('domain', $domain);
                }
            });
        }

        foreach ($urls->get() as $url) {
            $this->line('Dispatching: '.$url->href);
            $url->dispatch();
        }
    }
}
