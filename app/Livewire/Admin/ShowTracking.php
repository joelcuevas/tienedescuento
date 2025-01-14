<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowTracking extends Component
{
    public function render()
    {
        $products = Auth::user()
            ->products()
            ->orderBy('user_product.created_at', 'desc')
            ->paginate();

        return view('livewire.admin.show-tracking')->with([
            'products' => $products,
        ]);
    }
}
