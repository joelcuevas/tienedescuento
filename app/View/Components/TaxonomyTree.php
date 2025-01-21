<?php

namespace App\View\Components;

use App\Models\Store;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class TaxonomyTree extends Component
{
    public function __construct(
        public ?Store $store = null
    ) {}

    public function render(): View|Closure|string
    {
        $storeCondition = $this->store->id ? 'AND store_id = '.$this->store->id : '';

        $taxonomyTree = DB::select("
            WITH RECURSIVE taxonomy_cte AS (
                SELECT slug, title, CAST(NULL AS CHAR(255)) AS parent_slug, `order`
                FROM taxonomies
                WHERE parent_id IS NULL {$storeCondition}
                UNION ALL
                SELECT t.slug, t.title, CAST(cte.slug AS CHAR(255)) AS parent_slug, t.`order`
                FROM taxonomies t
                INNER JOIN taxonomy_cte cte ON t.parent_id = (
                    SELECT id FROM taxonomies WHERE slug = cte.slug {$storeCondition} LIMIT 1
                )
            )
            SELECT DISTINCT slug, title, parent_slug, `order`
            FROM taxonomy_cte
            ORDER BY `order`
        ");

        $taxonomies = $this->buildTree($taxonomyTree);

        return view('components.taxonomy-tree')->with([
            'taxonomies' => $taxonomies,
        ]);
    }

    private function buildTree($items, $parentSlug = null) {
        $tree = [];

        foreach ($items as $item) {
            if ($item->parent_slug == $parentSlug) {
                $tree[] = [
                    'slug' => $item->slug,
                    'title' => $item->title,
                    'children' => $this->buildTree($items, $item->slug),
                ];
            }
        }

        return $tree;
    }
}
