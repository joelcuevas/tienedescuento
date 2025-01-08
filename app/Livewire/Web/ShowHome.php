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
        return view('livewire.web.show-home')->with([
            'stores' => Store::orderBy('name')->get(),
            'taxonomies' => config('params.home_taxonomies.'.request()->countryCode),
        ]);
    }
}
