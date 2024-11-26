<?php

namespace App\Jobs\Liverpool;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class FetchProduct implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $url,
    ) {}

    public function handle(): void
    {
        $response = Http::withHeaders([
            'User-Agent' => 'PostmanRuntime/7.42.0',
            'Accept' => '*/*',
        ])->get($this->url);

        if ($response->status() == 200) {
            $dom = new Crawler($response->body());
            $data = $dom->filter('#__NEXT_DATA__');

            if ($data->count() > 0) {
                $results = json_decode($data->text());

                if (isset($results->query->data->mainContent)) {
                    $mainContent = $results->query->data->mainContent;

                    // save products
                    if (isset($mainContent->records)) {
                        foreach ($mainContent->records as $record) {
                            SaveProduct::dispatch($record);
                        }
                    }
                }
            }
        }
    }
}
