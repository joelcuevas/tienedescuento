<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Taxonomy;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TaxonomyBuild extends Command
{
    protected $signature = 'taxonomy:build';

    protected $description = 'Command description';

    public function handle()
    {
        $config = config('taxonomy');

        foreach ($config as $country => $taxons) {
            $this->build($country, $taxons);
        }
    }

    private function build(string $country, array $taxons, int $parentId = null)
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

            $query = Category::query();

            foreach ($config['keywords'] as $keyword) {
                $query->orWhere('slug', 'like', '%'.$keyword.'%');
            }

            $categories = $query->select('id')->distinct()->get()->pluck('id')->all();
            $taxonomy->categories()->sync($categories);

            if (isset($config['children']) && count($config['children'])) {
                $this->build($country, $config['children'], $taxonomy->id);
            }
        }
    }
}
