<?php

namespace App\View\Components;

use App\Models\Taxonomy;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaxonomyTree extends Component
{
    public function __construct() {}

    public function render(): View|Closure|string
    {
        $taxonomies = Taxonomy::query()
            ->whereCountry(request()->countryCode)
            ->whereNull('parent_id')
            ->with('subtaxonomies')
            ->get();

        return view('components.taxonomy-tree')->with([
            'taxonomies' => $taxonomies,
        ]);
    }
}
