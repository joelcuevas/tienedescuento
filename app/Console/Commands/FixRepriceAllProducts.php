<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class FixRepriceAllProducts extends Command
{
    protected $signature = 'fix:reprice';

    protected $description = 'Run mid-term pricer for all products';

    public function handle()
    {
        $products = Product::query()
            ->where(function ($q) {
                $q->whereNull('pricer_class');
                $q->orWhere('pricer_class', '!=', 'MidTermDropPricer@1.0.2');
            })
            ->orderByDesc('discount')
            ->limit(100)
            ->get();

        foreach ($products as $product) {
            dispatch(function () use ($product) {
                $product->updatePrices();
            });
        }
    }
}
