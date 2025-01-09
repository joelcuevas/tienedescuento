<?php

namespace App\Console\Commands;

use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class UrlScheduled extends Command
{
    protected $signature = 'url:scheduled {--limit=50} {--domain=}';

    protected $description = 'Retrieve scheduled URLs for crawling';

    public function handle()
    {
        $query = Url::query();

        if ($this->option('domain')) {
            $query->where('domain', $this->option('domain'));
        }

        // fetch relegated urls within 30% of the limit
        $limit = $this->option('limit');
        $pct30 = round($limit * 0.3, 0);
        $relegated = clone($query)->relegated($pct30)->get();

        // adjust the limit if relegated urls exist
        if ($relegated->count()) {
            $limit -= $pct30;
        }

        // fetch scheduled urls and merge with relegated urls
        $urls = $query->scheduled($limit)->get()->merge($relegated);

        foreach ($urls as $url) {
            $this->line("[URL {$url->id}] {$url->href}");
            $url->dispatch();
        }
    }
}
