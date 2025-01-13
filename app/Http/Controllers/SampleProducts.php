<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class SampleProducts extends Controller
{
    const SAMPLE_DAYS = 90;

    public function __invoke(Request $request)
    {
        if ($request->header('X-Dev-Mode') !== config('dev.mode_code')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $sampleStore = Store::where('slug', 'sample')->first();
        $productId = $request->query('product_id');

        if ($productId) {
            $product = Product::where('id', $productId)
                ->with(['categories', 'prices' => fn ($q) => $q->where('priced_at', '>=', now()->subDays(self::SAMPLE_DAYS))])
                ->first();

            if (! $product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json($this->formatProduct($product));
        }

        $products = Product::query()
            //->where('discount', '>', 0)
            ->when($sampleStore, fn ($query) => $query->where('store_id', '<>', $sampleStore->id))
            ->with(['categories', 'prices' => fn ($q) => $q->where('priced_at', '>=', now()->subDays(self::SAMPLE_DAYS))])
            //->has('prices', '>=', 15)
            ->orderByDesc('priced_at')
            ->limit(25)
            ->get();

        $response = $products->map(fn ($product) => $this->formatProduct($product));

        return response()->json($response);
    }

    private function formatProduct(Product $product): array
    {
        return [
            'sku' => $product->sku,
            'title' => $product->title,
            'brand' => $product->brand,
            'category' => $product->category,
            'external_url' => $product->external_url,
            'image_url' => $product->image_url,
            'prices' => $product->prices->map(fn ($price) => [
                'priced_at' => $price->priced_at->toDateTimeString(),
                'price' => $price->price,
            ])->toArray(),
        ];
    }
}
