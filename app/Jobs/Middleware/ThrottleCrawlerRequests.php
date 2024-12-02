<?php

namespace App\Jobs\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class ThrottleCrawlerRequests
{
    public function __construct(
        private string $domain,
    ) {}

    public function handle(object $job, Closure $next): void
    {
        Redis::throttle('throttle-'.$this->domain)
            ->block(0)
            ->allow(30)
            ->every(60)
            ->then(
                fn () => $next($job),
                fn () => $job->release(5),
            );
    }
}
