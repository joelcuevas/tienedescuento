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

class Category extends Model
{
    use HasFactory;

    protected $attributes = [
        'views' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            $category->title = Str::limit($category->title, 250);
            $category->slug = Str::slug($category->title);
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public static function scopeWhereSlugTree(Builder $query, string $slug): Builder
    {
        $categoryIds = collect(DB::select('
            WITH RECURSIVE category_hierarchy AS (
                SELECT id, parent_id, slug
                FROM categories
                WHERE slug = ?

                UNION ALL

                SELECT c.id, c.parent_id, c.slug
                FROM categories c
                INNER JOIN category_hierarchy ch ON c.parent_id = ch.id
            )
            SELECT id FROM category_hierarchy
        ', [$slug]))->pluck('id')->toArray();

        return $query->whereIn('id', $categoryIds);
    }
}
