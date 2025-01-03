<?php

namespace App\Jobs;

use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResolveUrl implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $href,
        protected int $priority = 99,
    ) {}

    public function handle(): void
    {
        Url::resolve($this->href, $this->priority);
    }
}
