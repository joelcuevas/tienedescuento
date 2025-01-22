<?php

namespace App\View\Components;

use App\Models\Store;
use App\Models\Taxonomy;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CatalogMenu extends Component
{
    public $taxonomies;

    public $stores;

    public function __construct()
    {
        $this->taxonomies = Taxonomy::query()
            ->whereCountry(request()->countryCode)
            ->whereNull('parent_id')
            ->select('title', 'slug', 'order')
            ->distinct('slug')
            ->orderBy('order')
            ->take(3)
            ->get();

        $this->stores = Store::query()
            ->whereCountry(request()->countryCode)
            ->orderBy('priority')
            ->take(3)
            ->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.catalog-menu');
    }
}
