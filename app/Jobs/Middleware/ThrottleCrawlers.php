<?php

namespace App\Jobs\Middleware;

use App\Models\Url;
use Closure;
use Illuminate\Support\Facades\Redis;

class ThrottleCrawlers
{
    public function __construct(
        private string $domain,
    ) {}

    public function handle(object $job, Closure $next): void
    {
        // throttle if one third of recent requests have failed
        $recent = Url::query()
            ->whereDomain($this->domain)
            ->where('crawled_at', '>', now()->subMinutes(15))
            ->orderByDesc('crawled_at')
            ->limit(50)
            ->get();

        $blocked = $recent->filter(fn ($url) => ! in_array($url->status, Url::SAFE_STATUSES));

        if ($blocked->count() > $recent->count() / 3) {
            $job->release(3600);

            return;
        }

        // throttle according to each domain configuration
        $slug = str_replace('.', '-', $this->domain);
        $allow = config("descuento.crawler.domains.{$slug}.throttle.allow", 60);
        $every = config("descuento.crawler.domains.{$slug}.throttle.every", 60);

        Redis::throttle('throttle-'.$slug)
            ->block(0)
            ->allow($allow)
            ->every($every)
            ->then(
                fn () => $next($job),
                fn () => $job->release(30),
            );
    }
}
