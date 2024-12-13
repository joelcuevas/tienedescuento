<?php

namespace App\Jobs\Middleware;

use App\Models\Url;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class ThrottleCrawlers
{
    public function __construct(
        private Url $url,
    ) {}

    public function handle(object $job, Closure $next): void
    {
        // throttle if one third of recent requests have failed
        $recent = Url::query()
            ->whereDomain($this->url->domain)
            ->where('crawled_at', '>', now()->subMinutes(15))
            ->orderByDesc('crawled_at')
            ->limit(50)
            ->get();

        $blocked = $recent->filter(fn ($url) => $url->status >= 300);

        if ($recent->count() > 50 && $blocked->count() > $recent->count() / 3) {
            // release for an hour later
            $job->release(3600);

            return;
        }

        // throttle by time according to each domain configuration
        $allow = $this->url->getCrawlerConfig('throttle_allow', 60);
        $every = $this->url->getCrawlerConfig('throttle_every', 60);

        Redis::throttle('throttle-'.$this->url->domain)
            ->block(0)
            ->allow($allow)
            ->every($every)
            ->then(
                fn () => $next($job),
                fn () => $job->release(30),
            );
    }
}
