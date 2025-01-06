<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class FeaturedProducts extends Component
{
    public string $country;

    public string $taxonomy;

    public function mount(string $country, string $taxonomy)
    {
        $this->country = $country;
        $this->taxonomy = $taxonomy;
    }

    public function render()
    {
        $query = Product::whereTaxonomy($this->country, $this->taxonomy);

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
