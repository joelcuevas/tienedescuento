<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Str;
use Livewire\Component;

class ShowHome extends Component
{
    public function render()
    {
        $hot = ['celulares', 'tablets', 'laptops', 'pantallas', 'refrigeradores'];
        $featured = [];

        foreach ($hot as $category) {
            $title = Str::title($category);

            if (config('app.env') == 'production') {
                $query = Product::whereCategory($category);
            } else {
                $query = Product::inRandomOrder();
            }

            $products = $query
                ->with('store')
                ->recent()
                ->orderByDesc('discount')
                ->take(6)
                ->get();

            $featured[$category] = [
                'title' => $title,
                'products' => $products,
            ];
        }

        return view('livewire.web.show-home')->with([
            'stores' => Store::orderBy('name')->get(),
            'featured' => $featured,
        ]);
    }
}
