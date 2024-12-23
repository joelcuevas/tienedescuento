<?php

namespace App\Livewire\Web;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Str;

class ShowHome extends Component
{
    public function render()
    {
        $hot = ['celulares', 'tablets', 'laptops', 'pantallas', 'refrigeradores'];
        $featured = [];

        foreach ($hot as $category) {
            $title = Str::title($category);

            $featured[$title] = Product::query()
                ->whereCategory($category)
                ->orderByDesc('discount')
                ->take(6)
                ->get();
        }

        return view('livewire.web.show-home')->with([
            'featured' => $featured,
        ]);
    }
}
