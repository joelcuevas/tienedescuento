<?php

namespace App\Jobs;

use App\Jobs\Middleware\ThrottleCrawlers;
use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CrawlUrl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Url $url,
    ) {}

    public function uniqueId(): string
    {
        return $this->url->hash;
    }

    public function handle(): void
    {
        $this->url->crawl();
    }

    //public function middleware(): array
    //{
    //    return [new ThrottleCrawlers($this->url)];
    //}
}
