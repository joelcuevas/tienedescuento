<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Taxonomy;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TaxonomyBuild extends Command
{
    protected $signature = 'taxonomy:build';

    protected $description = 'Builds a hierarchical taxonomy tree for categories';

    public function handle()
    {
        $config = config('taxonomy');

        foreach ($config as $country => $taxons) {
            $this->build($country, $taxons);
        }
    }

    private function build(string $country, array $taxons, int $parentId = null, array $topCategoriesIds = [])
    {
        $order = -1;

        foreach ($taxons as $taxon => $config) {
            $slug = Str::slug($taxon);
            $order++;

            $taxonomy = Taxonomy::firstOrCreate([
                'country' => $country,
                'slug' => $slug,
            ], [
                'title' => $taxon,
                'parent_id' => $parentId,
                'order' => $order,
            ]);

            $globalKeywords = array_filter($config['keywords'], function($k) {
                return ! str_starts_with($k, '>');
            });

            $nestedKeywords = array_map(function($k) {
                return substr($k, 1);
            }, array_filter($config['keywords'], function($k) {
                return str_starts_with($k, '>');
            }));

            $globalIds = [];
            $nestedIds = [];

            if (count($globalKeywords)) {
                $globalIds = Category::query()
                    ->whereIn('slug', $globalKeywords)
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

            $categoryIds = [...$globalIds, ...$nestedIds];
            $taxonomy->categories()->sync($categoryIds);

            if (isset($config['children']) && count($config['children'])) {
                $this->build($country, $config['children'], $taxonomy->id, $categoryIds);
            }
        }
    }
}
