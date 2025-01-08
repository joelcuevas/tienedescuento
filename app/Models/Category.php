<?php

namespace App\Models;

use Exception;
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

    public static function scopeWhereIsChildOf(Builder $query, mixed $scope, int $maxDepth = 3): Builder
    {
        $condition = 'false';
        $params = [];

        if (is_int($scope)) {
            $scope = [$scope];
        }

        if (is_array($scope)) {
            if (count($scope) == 0) {
                $scope = [-1];
            }

            $placeholders = implode(',', array_fill(0, count($scope), '?'));
            $condition = "id in ({$placeholders})";
            $params = [...$scope, $maxDepth];
        } elseif (is_string($scope)) {
            $condition = 'slug = ?';
            $params = [$scope, $maxDepth];
        } else {
            throw new Exception('Invalid scope format');
        }

        $categoryIds = collect(DB::select("
            WITH RECURSIVE category_hierarchy AS (
                SELECT id, parent_id, slug, 1 AS depth
                FROM categories
                WHERE {$condition}

                UNION ALL

                SELECT c.id, c.parent_id, c.slug, ch.depth + 1
                FROM categories c
                INNER JOIN category_hierarchy ch ON c.parent_id = ch.id
                WHERE ch.depth < ?
            )
            SELECT id FROM category_hierarchy
        ", $params))->pluck('id')->toArray();

        return $query->whereIn('id', $categoryIds);
    }
}
