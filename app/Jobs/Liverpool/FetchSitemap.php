<?php

namespace App\Jobs\Liverpool;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class FetchSitemap implements ShouldQueue
{
    use Queueable;

    private $sitemapUrl = 'https://www.liverpool.com.mx/sitemap/sitemap.xml';

    public function handle(): void
    {
        $this->fetchSitemaps($this->sitemapUrl);
    }

    private function fetchSitemaps($url): void
    {
        $response = Http::withHeaders([
            'User-Agent' => 'PostmanRuntime/7.42.0',
            'Accept' => '*/*',
        ])->get($url);

        if ($response->status() == 200) {
            $sitemap = new SimpleXMLElement($response->body());

            foreach ($sitemap as $type => $node) {
                $url = $node->loc;

                if ($type == 'sitemap') {
                    $this->fetchSitemaps($url);
                } elseif ($type == 'url') {
                    $path = parse_url($url, PHP_URL_PATH);

                    if (preg_match('/\/cat[^\/]*$/', $path)) {
                        FetchCategory::dispatch($url);
                    } elseif (preg_match('/^\/tienda\/pdp\//', $path)) {
                        FetchProduct::dispatch($url);
                    }
                }
            }
        }
    }
}
