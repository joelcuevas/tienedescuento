<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Store;
use App\Models\Taxonomy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxonomyBuilder
{
    public function __construct()
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            $config = config("taxonomy.{$store->country}.{$store->slug}");

            if ($config) {
                $this->build($store->country, $store, $config);
            }
        }

        // clear taxonomies without children
        $orphanIds = Taxonomy::whereDoesntHave('subtaxonomies')->whereNull('parent_id')->pluck('id');
        DB::table('category_taxonomy')->whereIn('taxonomy_id', $orphanIds)->delete();
        Taxonomy::whereIn('id', $orphanIds)->delete();
    }

    private function build(string $country, ?Store $store, array $taxons, ?int $parentId = null, string $prefix = '', array $scopeIds = [])
    {
        $order = -1;
        $childrenIds = [];

        foreach ($taxons as $taxon => $config) {
            $slug = Str::slug($taxon);
            $order++;

            $taxonomy = Taxonomy::updateOrCreate([
                'country' => $country,
                'store_id' => $store?->id,
                'slug' => $prefix.$slug,
            ], [
                'title' => $taxon,
                'parent_id' => $parentId,
                'order' => $order,
            ]);

            $childrenIds[] = $taxonomy->id;
            $childrenPrefix = $prefix.$taxonomy->slug.'-';

            // keywords starting with * can be ANY category with this slug
            $globalKeywords = $this->extractModifiedKeywords('*', $config['keywords']);

            // keywords starting with # are specific category codes
            $categoryKeywords = $this->extractModifiedKeywords('#', $config['keywords']);

            // keywords starting with - must be removed from the final set
            $bannedKeywords = $this->extractModifiedKeywords('-', $config['keywords']);

            // keywords with no modifiers, must be children of scope categories
            $nestedKeywords = array_filter($config['keywords'], function ($k) {
                return ! in_array(substr($k, 0, 1), ['*', '#', '-']);
            });

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

            if (count($categoryKeywords)) {
                $categoryCodeIds = Category::query()
                    ->whereIn('code', $categoryKeywords)
                    ->select('id')
                    ->distinct()
                    ->pluck('id')
                    ->all();

                $categoryIds = [...$categoryIds, ...$categoryCodeIds];
            }

            if (count($nestedKeywords)) {
                $nestedIds = Category::query()
                    ->when(count($scopeIds), fn ($q) => $q->whereIsChildOf($scopeIds))
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
                $this->build($country, $store, $config['children'], $taxonomy->id, $childrenPrefix, $categoryIds);
            }
        }

        // clean up old children
        Taxonomy::query()
            ->whereParentId($parentId)
            ->whereNotIn('id', $childrenIds)
            ->update(['parent_id' => null]);
    }

    private function extractModifiedKeywords(string $prefix, array $keywords)
    {
        return array_map(function ($k) use ($prefix) {
            return substr($k, strlen($prefix));
        }, array_filter($keywords, function ($k) use ($prefix) {
            return str_starts_with($k, $prefix);
        }));
    }
}