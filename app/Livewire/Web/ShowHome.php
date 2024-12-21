<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Component;

class ShowHome extends Component
{
    public function render()
    {
        $products = Product::with(['store', 'categories'])
            ->take(36)
            ->orderByDesc('discount')
            ->get();

        return view('livewire.web.show-home')->with([
            'products' => $products,
        ]);
    }
}
