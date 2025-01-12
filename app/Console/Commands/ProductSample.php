<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class ProductSample extends Command
{
    protected $signature = 'product:sample {--stores} {--production}';

    protected $description = 'Fetches sample products from stores or production servers';

    public function handle()
    {
        if (! config('dev.mode_code') || ! config('dev.sampling_server')) {
            $this->error('Configure dev mode code and sampling server in .env file.');

            return;
        }

        if ($this->option('production')) {
            $samplingServer = rtrim(config('dev.sampling_server'), '/');

            $samples = Http::withHeaders([
                'X-Dev-Mode' => config('dev.mode_code'),
            ])->get($samplingServer . '/api/products/sample');

            $store = Store::firstOrCreate([
                'country' => 'mx',
                'slug' => 'sample',
            ], [
                'name' => 'Sample',
                'external_url' => 'https://example.com',
            ]);

            foreach ($samples->json() as $sample) {
                $this->line('Sampling: '.$sample['sku'].' '.$sample['title']);

                $product = Product::whereStoreId($store->id)->whereSku($sample['sku'])->first();

                if ($product) {
                    continue;
                }

                $product = Product::create([
                    'sku' => $sample['sku'],
                    'title' => $sample['title'],
                    'brand' => $sample['brand'],
                    'external_url' => $sample['external_url'],
                    'image_url' => $sample['image_url'],
                    'store_id' => $store->id,
                ]);

                $categoryCode = substr(sha1($sample['category']), 0, 8);

                $category = Category::firstOrCreate([
                    'store_id' => $store->id,
                    'code' => $categoryCode,
                ], [
                    'title' => $sample['category'],
                    'external_url' => 'https://example.com/category/'.$categoryCode,
                ]);

                $product->categories()->sync($category);

                foreach ($sample['prices'] as $price) {
                    $product->prices()->create([
                        'priced_at' => new Carbon($price['priced_at']),
                        'price' => $price['price'],
                        'source' => 'prod',
                    ]);
                };
            }
        } elseif ($this->option('stores')) {
            Artisan::call('url:crawl "https://www.liverpool.com.mx/tienda/pdp/samsung-galaxy-s24-fe-dynamic-amoled-2x-6.7-pulgadas-desbloqueado/1164289479?skuid=1164289482"');
            Artisan::call('url:crawl "https://www.costco.com.mx/rest/v2/mexico/products/685767/?fields=FULL&lang=es_MX&curr=MXN"');
            Artisan::call('url:crawl "https://www.liverpool.com.mx/tienda/celulares/cat5150024"');
            Artisan::call('url:crawl "https://www.costco.com.mx/rest/v2/mexico/products/search?category=cos_1.3.1&currentPage=0&pageSize=25&lang=es_MX&curr=MXN&fields=FULL"');
        } else {
            $this->error('Please specify --stores or --production option');
        }
    }
}
