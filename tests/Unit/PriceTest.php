<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_product_price_fields(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->for($store)->create();

        $product->prices()->create([
            'price' => '99.99',
            'priced_at' => now()->subDays(2),
            'source' => 'foo',
        ]);

        $product->prices()->create([
            'price' => '299.99',
            'priced_at' => now()->subDays(1),
            'source' => 'foo',
        ]);

        $product->prices()->create([
            'price' => '199.99',
            'priced_at' => now(),
            'source' => 'foo',
        ]);

        $product->refresh();
        $this->assertEquals('199.99', $product->latest_price);
        $this->assertEquals('299.99', $product->maximum_price);
        $this->assertEquals('99.99', $product->minimum_price);
    }

    public function test_it_keeps_the_minumum_price_for_a_date(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->for($store)->create();

        $product->prices()->create([
            'price' => '99.99',
            'priced_at' => now(),
            'source' => 'foo',
        ]);

        $product->prices()->create([
            'price' => '199.99',
            'priced_at' => now(),
            'source' => 'foo',
        ]);

        $product->refresh();
        $this->assertEquals(1, $product->prices()->count());
        $this->assertEquals('99.99', $product->latest_price);
        $this->assertEquals('99.99', $product->maximum_price);
        $this->assertEquals('99.99', $product->minimum_price);
    }

    public function test_it_sets_priced_at_to_now_by_default(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->for($store)->create();

        $priceNow = $product->prices()->create([
            'price' => '99.99',
            'source' => 'foo',
        ]);

        $this->assertEquals(now()->format('Y-m-d'), $priceNow->priced_at->format('Y-m-d'));

        $priceOld = $product->prices()->create([
            'price' => '99.99',
            'source' => 'foo',
            'priced_at' => now()->subDay(),
        ]);

        $this->assertEquals(now()->subDay()->format('Y-m-d'), $priceOld->priced_at->format('Y-m-d'));
    }
}
