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
            ->orderByDesc('discount')
            ->paginate(30);

        return view('livewire.web.show-home')->with([
            'products' => $products,
        ]);
    }
}
