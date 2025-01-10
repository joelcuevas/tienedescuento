<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\Taxonomy;
use Livewire\Component;

class FeaturedProducts extends Component
{
    public string $country;

    public string $taxonomy;

    public function mount(string $country, string $taxonomy)
    {
        $this->country = $country;
        $this->taxonomy = $taxonomy;
    }

    public function placeholder(array $params = [])
    {
        return view('components.featured-products-skeleton', $params);
    }

    public function render()
    {
        $taxonomy = Taxonomy::query()
            ->whereCountry($this->country)
            ->whereSlug($this->taxonomy)
            ->first();

        if ($taxonomy) {
            $products = Product::query()
                ->whereTaxonomy($taxonomy)
                ->with('store')
                ->recent()
                ->orderByDesc('discount')
                ->take(6)
                ->get();
        }

        return view('livewire.web.featured-products')->with([
            'title' => $taxonomy?->title,
            'products' => $products ?? collect([]),
        ]);
    }
}
