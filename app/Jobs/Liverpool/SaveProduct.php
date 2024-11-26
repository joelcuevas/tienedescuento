<?php

namespace App\Jobs\Liverpool;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SaveProduct implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private object $record,
    ) {}

    public function handle(): void
    {
        $meta = $this->record?->allMeta;

        if ($meta && $meta?->id) {
            $price = $meta?->minimumPromoPrice ?? $meta?->minimumListPrice;

            if ($price) {
                $liverpool = Store::whereSlug('liverpool')->firstOrFail();
                $url = 'https://www.liverpool.com.mx/tienda/pdp/-/'.$meta->id;
                $imageUrl = $this->findImageUrl($meta);

                $product = Product::updateOrCreate([
                    'store_id' => $liverpool->id,
                    'sku' => $meta->id,
                ], [
                    'brand' => $meta->brand ?? 'NA',
                    'title' => $meta->title,
                    'url' => $url,
                    'image_url' => $imageUrl,
                ]);

                $product->addPrice($price, source: 'liverpool');
            }
        }
    }

    private function findImageUrl(object $meta): ?string
    {
        if (isset($meta->productImages[0])) {
            foreach ($meta->productImages as $img) {
                if (isset($img->thumbnailImage)) {
                    return $img->thumbnailImage;
                }
            }
        }
    }
}
