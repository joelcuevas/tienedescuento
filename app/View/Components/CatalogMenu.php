<?php

namespace App\View\Components;

use App\Models\Store;
use App\Models\Taxonomy;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CatalogMenu extends Component
{
    public $stores;

    public function __construct()
    {
        $this->stores = Store::query()
            ->whereCountry(request()->countryCode)
            ->orderBy('priority')
            ->take(5)
            ->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.catalog-menu');
    }
}
