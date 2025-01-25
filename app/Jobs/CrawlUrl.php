<?php

namespace App\Jobs;

use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CrawlUrl implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(
        protected Url $url,
    ) {}

    public function uniqueId(): string
    {
        return $this->url->id;
    }

    public function handle(): void
    {
        $this->url->crawl();
    }
}
