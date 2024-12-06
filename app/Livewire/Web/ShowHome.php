<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.web')]
class ShowHome extends Component
{
    use WithPagination;

    public function render()
    {
        $products = Product::with(['store', 'categories'])
            ->orderByDesc('discount')
            ->paginate(20);

        return view('livewire.web.show-home')->with([
            'products' => $products,
        ]);
    }
}
