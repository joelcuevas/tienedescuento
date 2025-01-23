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
        $this->url->crawl();
    }
}
