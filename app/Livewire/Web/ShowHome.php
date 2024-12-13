<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ShowHome extends Component
{
    public function render()
    {
        $products = Product::with(['store', 'categories'])
            ->where('priced_at', '>=', now()->subHours(12)->startOfDay())
            ->take(30)
            ->orderByDesc('discount')
            ->get();

        return view('livewire.web.show-home')->with([
            'products' => $products,
        ]);
    }
}
