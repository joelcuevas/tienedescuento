<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Pricers\MidTermDropPricer;
use Illuminate\Console\Command;

class ProductPrice extends Command
{
    protected $signature = 'product:price {product_id}';

    protected $description = 'Update the prices of a product using the default pricer';

    public function handle()
    {
        $product = Product::find($this->argument('product_id'));

        if (! $product) {
            $this->error('Product not found.');

            return;
        }

        $pricer = new MidTermDropPricer($product);
        $pricer->price();
    }
}
