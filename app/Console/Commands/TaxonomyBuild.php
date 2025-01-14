<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Taxonomy;
use Illuminate\Console\Command;
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

        // crear taxonomies without children
        $orphanIds = Taxonomy::whereDoesntHave('subtaxonomies')->whereNull('parent_id')->pluck('id');
        DB::table('category_taxonomy')->whereIn('taxonomy_id', $orphanIds)->delete();
        Taxonomy::whereIn('id', $orphanIds)->delete();
    }

    private function build(string $country, array $taxons, ?int $parentId = null, string $prefix = '', array $topCategoriesIds = [])
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

            // keywords without modifiers, can be ANY category with this slug
            $globalKeywords = array_filter($config['keywords'], function ($k) {
                return ! in_array(substr($k, 0, 1), ['>', '#', '-']);
            });

            // keywords starting with # are direct category ids
            $codeKeywords = array_map(function ($k) {
                return substr($k, 1);
            }, array_filter($config['keywords'], function ($k) {
                return str_starts_with($k, '#');
            }));

            // keywords starting with > must be children of parent taxonomy categories
            $nestedKeywords = array_map(function ($k) {
                return substr($k, 1);
            }, array_filter($config['keywords'], function ($k) {
                return str_starts_with($k, '>');
            }));

            // keywords starting with - must be removed from the final set
            $bannedKeywords = array_map(function ($k) {
                return substr($k, 1);
            }, array_filter($config['keywords'], function ($k) {
                return str_starts_with($k, '-');
            }));

            $globalIds = [];
            $codeIds = [];
            $nestedIds = [];
            $categoryIds = [];

            if (count($globalKeywords)) {
                $globalIds = Category::query()
                    ->whereIn('slug', $globalKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();

                $categoryIds = [...$categoryIds, ...$globalIds];
            }

            if (count($codeKeywords)) {
                $codeIds = Category::query()
                    ->whereIn('code', $codeKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();

                $categoryIds = [...$categoryIds, ...$codeIds];
            }

            if (count($nestedKeywords)) {
                $nestedIds = Category::query()
                    ->when(count($topCategoriesIds), fn ($q) => $q->whereIsChildOf($topCategoriesIds))
                    ->whereIn('slug', $nestedKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();

                $categoryIds = [...$categoryIds, ...$nestedIds];
            }

            $finalCategoryIds = Category::query()
                ->whereIsChildOf($categoryIds, 3)
                ->when(count($bannedKeywords), fn ($q) => $q->whereNotIn('slug', $bannedKeywords))
                ->select('id')
                ->distinct()
                ->pluck('id')
                ->all();

            $taxonomy->categories()->sync($finalCategoryIds);

            if (isset($config['children']) && count($config['children'])) {
                $this->build($country, $config['children'], $taxonomy->id, $subPrefix, $categoryIds);
            }
        }

        // clean up old children
        Taxonomy::query()
            ->whereParentId($parentId)
            ->whereNotIn('id', $childrenIds)
            ->update(['parent_id' => null]);
    }
}
