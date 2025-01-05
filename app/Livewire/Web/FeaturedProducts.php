<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class FeaturedProducts extends Component
{
    public string $category;

    public function mount(string $category)
    {
        $this->category = $category;
    }

    public function render()
    {
        if (config('app.env') == 'production') {
            $query = Product::whereCategory($this->category);
        } else {
            $query = Product::inRandomOrder();
        }

        $products = $query
            ->with('store')
            ->recent()
            ->orderByDesc('discount')
            ->take(6)
            ->get();

        return view('livewire.web.featured-products')->with([
            'title' => ucwords($this->category),
            'products' => $products,
        ]);
    }
}
