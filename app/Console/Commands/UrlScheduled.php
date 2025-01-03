<?php

namespace App\Console\Commands;

use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class UrlScheduled extends Command
{
    protected $signature = 'url:scheduled {--limit=50} {--domain=*}';

    protected $description = 'Retrieve scheduled URLs from the database for crawling';

    public function handle()
    {
        $query = Url::query();

        if ($this->option('domain')) {
            $query->where(function (Builder $query) {
                foreach ($this->option('domain') as $domain) {
                    $query->orWhere('domain', $domain);
                }
            });
        }

        $urls = $query->scheduled($this->option('limit'));

        foreach ($urls->get() as $url) {
            $this->line("[URL {$url->id}] {$url->href}");
            $url->dispatch();
        }
    }
}
