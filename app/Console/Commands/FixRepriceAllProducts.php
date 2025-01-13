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
        $products = Product::whereNull('pricer_class')
            ->orderByDesc('discount')
            ->limit(1000)
            ->get();
        
        foreach ($products as $product) {
            dispatch(function() use ($product) {
                $product->updatePrices();
            });
        }
    }
}
