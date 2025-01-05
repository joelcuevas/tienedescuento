<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class FeaturedProducts extends Component
{
    public string $taxonomy;

    public function mount(string $taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function render()
    {
        if (config('app.env') == 'production') {
            $query = Product::whereTaxonomy($this->taxonomy);
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
            'title' => ucwords($this->taxonomy),
            'products' => $products,
        ]);
    }
}
