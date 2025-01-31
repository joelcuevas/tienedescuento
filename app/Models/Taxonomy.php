<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Taxonomy extends Model
{
    use HasFactory;

    public $attributes = [
        'order' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (Taxonomy $taxonomy) {
            $taxonomy->title = Str::limit($taxonomy->title, 250);

            if (! $taxonomy->slug) {
                $taxonomy->slug = Str::slug($taxonomy->title);
            }
        });
    }

    public function link(): string
    {
        return route('catalogs.index', [$this->slug]);
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
}
