<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Taxonomy;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomyBuild extends Command
{
    protected $signature = 'taxonomy:build';

    protected $description = 'Build a hierarchical taxonomy tree for categories';

    public function handle()
    {
        $config = config('taxonomy');

        foreach ($config as $country => $taxons) {
            $this->build($country, $taxons);
        }
    }

    private function build(string $country, array $taxons, int $parentId = null, string $prefix = '', array $topCategoriesIds = [])
    {
        $order = -1;
        $childrenIds = [];

        foreach ($taxons as $taxon => $config) {
            $slug = Str::slug($taxon);
            $order++;

            $taxonomy = Taxonomy::updateOrCreate([
                'country' => $country,
                'slug' => $prefix.$slug,
            ], [
                'title' => $taxon,
                'parent_id' => $parentId,
                'order' => $order,
            ]);

            $childrenIds[] = $taxonomy->id;
            $subPrefix = $prefix.$taxonomy->slug.'-';

            $globalKeywords = array_filter($config['keywords'], function($k) {
                return ! in_array(substr($k, 1), ['>', '#']);
            });

            $codeKeywords = array_map(function($k) {
                return substr($k, 1);
            }, array_filter($config['keywords'], function($k) {
                return str_starts_with($k, '#');
            }));

            $nestedKeywords = array_map(function($k) {
                return substr($k, 1);
            }, array_filter($config['keywords'], function($k) {
                return str_starts_with($k, '>');
            }));

            $globalIds = [];
            $codeIds = [];
            $nestedIds = [];

            if (count($globalKeywords)) {
                $globalIds = Category::query()
                    ->whereIn('slug', $globalKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();
            }

            if (count($codeKeywords)) {
                $codeIds = Category::query()
                    ->whereIn('code', $codeKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();
            }

            if (count($nestedKeywords)) {
                $nestedIds = Category::query()
                    ->when(count($topCategoriesIds), fn ($q) => $q->whereIsChildOf($topCategoriesIds))
                    ->whereIn('slug', $nestedKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();
            }

            $categoryIds = [...$globalIds, ...$codeIds, ...$nestedIds];
            $taxonomy->categories()->sync($categoryIds);

            if (isset($config['children']) && count($config['children'])) {
                $this->build($country, $config['children'], $taxonomy->id, $subPrefix, $categoryIds);
            }
        }

        Taxonomy::query()
            ->whereParentId($parentId)
            ->whereNotIn('id', $childrenIds)
            ->update(['parent_id' => null]);
    }
}
