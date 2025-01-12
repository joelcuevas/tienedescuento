<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class SampleProducts extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->header('X-Dev-Mode') !== config('dev.mode_code')) {
            //return response()->json(['message' => 'Unauthorized'], 401);
        }

        $sampleStore = Store::where('slug', 'sample')->first();

        $products = Product::query()
            ->when($sampleStore, fn ($query) => $query->where('store_id', '<>', $sampleStore->id))
            ->with(['categories', 'prices' => fn ($q) => $q->where('priced_at', '>=', now()->subDays(60))])
            ->whereRelation('prices', 'priced_at', '<=', now()->subDays(60))
            ->has('prices', '>=', 10)
            ->orderByDesc('priced_at')
            ->limit(10)
            ->get();
            
        $response = [];

        foreach ($products as $product) {
            $data = [
                'sku' => $product->sku,
                'title' => $product->title,
                'brand' => $product->brand,
                'category' => $product->category,
                'external_url' => $product->external_url,
                'image_url' => $product->image_url,
                'prices' => [],
            ];

            foreach ($product->prices as $price) {
                $data['prices'][] = [
                    'priced_at' => $price->priced_at->toDateTimeString(),
                    'price' => $price->price,
                ];
            }

            $response[] = $data;
        };

        return $response;
    }
}
