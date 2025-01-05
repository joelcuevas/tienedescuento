<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Taxonomy extends Model
{
    use HasFactory;

    public $attributes = [
        'order' =>  0,
    ];

    protected static function booted(): void
    {
        static::saving(function (Taxonomy $taxonomy) {
            $taxonomy->title = Str::limit($taxonomy->title, 250);
            $taxonomy->slug = Str::slug($taxonomy->title);
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id');
    }

    public function subtaxonomies(): HasMany
    {
        return $this->hasMany(Taxonomy::class, 'parent_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public static function scopeWhereSlugTree(Builder $query, string $slug, int $depth = 1): Builder
    {
        $taxonomyIds = collect(DB::select('
            WITH RECURSIVE taxonomy_hierarchy AS (
                SELECT id, parent_id, slug, 1 AS depth
                FROM taxonomies
                WHERE slug = ?

                UNION ALL

                SELECT t.id, t.parent_id, t.slug, th.depth + 1
                FROM taxonomies t
                INNER JOIN taxonomy_hierarchy th ON t.parent_id = th.id
                WHERE th.depth < ?
            )
            SELECT id FROM taxonomy_hierarchy
        ', [$slug, $depth]))->pluck('id')->toArray();

        return $query->whereIn('id', $taxonomyIds);
    }
}
