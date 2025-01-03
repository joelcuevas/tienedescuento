<?php

namespace App\Jobs;

use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CrawlUrl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Url $url,
    ) {}

    public function handle(): void
    {
        if ($this->url->scheduled_at->isAfter(now())) {
            return;
        }
        
        $this->url->crawl();
    }
}
