<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_assign_categories_to_products(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->for($store)->create();

        $product->addCategories([
            'cat1' => ['title' => 'cat1', 'url' => 'https://example.com/cat1'],
            'cat2' => ['title' => 'cat2', 'url' => 'https://example.com/cat2'],
            'cat3' => ['title' => 'cat3', 'url' => 'https://example.com/cat3'],
        ]);

        $this->assertEquals(3, $product->categories()->count());
        $this->assertEquals('cat3', $product->fresh()->category->title);

        $product->addCategories([
            'set1' => ['title' => 'set1', 'url' => 'https://example.com/set1'],
            'set2' => ['title' => 'set2', 'url' => 'https://example.com/set2'],
            'set3' => ['title' => 'set3', 'url' => 'https://example.com/set3'],
        ]);

        $this->assertEquals(6, $product->categories()->count());
        $this->assertEquals('set3', $product->fresh()->category->title);
    }
}
