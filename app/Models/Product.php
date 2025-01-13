<?php

namespace App\Models;

use App\Models\Traits\Product\HasPrices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use HasPrices;
    use Searchable;

    const DAYS_OUTDATED = 15;

    const PAGE_SIZE = 24;

    const MAX_PAGES = 25;

    protected $attributes = [
        'views' => 0,
        'is_active' => true,
    ];

    protected $casts = [
        'priced_at' => 'datetime',
        'priced_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            $product->title = Str::limit($product->title, 250);
            $product->slug = Str::slug($product->title);

            $product->brand = Str::limit($product->brand, 250);
            $product->brand_slug = $product->brand ? Str::slug($product->brand) : null;
        });

        static::saved(function (Product $product) {
            if ($product->wasChanged(['sku', 'title', 'brand'])) {
                $product->searchable();
            }
        });
    }

    protected function category(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->categories->last()->title,
        );
    }

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $this->isOutdated() ? 0 : $value,
        );
    }

    public function isOutdated(): bool
    {
        return ! $this->is_active || $this->priced_at < now()->subDays(self::DAYS_OUTDATED);
    }

    public function link(): string
    {
        return route('products.show', [$this->store->slug, $this->sku, $this->slug]);
    }

    public function storeLink(): string
    {
        return route('catalogs.store', [$this->store->slug]);
    }

    public function brandLink(): string
    {
        return route('catalogs.store_brand', [$this->store->slug, $this->brand_slug]);
    }

    public function categoryLink(): string
    {
        return route('catalogs.store_category', [$this->store->slug, $this->categories->last()->slug]);
    }

    public function categoryBrandLink(): string
    {
        return route('catalogs.store_category_brand', [$this->store->slug, $this->categories->last()->slug, $this->brand_slug]);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    public function searchableAs(): string
    {
        return 'products_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'store_name' => $this->store->name,
            'sku' => $this->sku,
            'title' => $this->title,
            'brand' => $this->brand,
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('store');
    }

    public static function scopeOnlyRecent(Builder $query): Builder
    {
        return $query->whereIsActive(true)->where('priced_at', '>=', now()->subDays(self::DAYS_OUTDATED));
    }

    public static function scopeWhereCategory(Builder $query, mixed $slug): Builder
    {
        $ids = Category::whereSlug($slug)->pluck('id')->all();

        return $query->whereCategoryIds($ids);
    }

    public static function scopeWhereTaxonomy(Builder $query, Taxonomy $taxonomy, int $depth = 3): Builder
    {
        $categoryIds = DB::table('category_taxonomy')
            ->whereTaxonomyId($taxonomy->id)
            ->pluck('category_id')
            ->unique()
            ->all();

        $categoryIds = Category::whereIsChildOf($categoryIds, $depth)
            ->select('id')
            ->pluck('id')
            ->unique()
            ->all();

        return $query->whereCategoryIds($categoryIds);
    }

    public static function scopeWhereCategoryIds(Builder $query, array $ids): Builder
    {
        return $query
            ->joinSub(
                DB::table('category_product')
                    ->select('product_id')
                    ->distinct()
                    ->forceIndex('category_product_category_id_product_id_index')
                    ->whereIn('category_id', $ids),
                'cp',
                'products.id',
                '=',
                'cp.product_id'
            );
    }
}
