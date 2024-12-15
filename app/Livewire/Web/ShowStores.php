<?php

namespace App\Livewire\Web;

use App\Models\Store;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ShowStores extends Component
{
    public function render()
    {
        $stores = Store::whereCountry(request()->countryCode)
            ->orderByDesc('views')
            ->paginate(30);

        return view('livewire.web.show-stores')->with([
            'stores' => $stores,
        ]);
    }
}
