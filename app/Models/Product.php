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

    const DAYS_OUTDATED = 2;

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
            if ($product->wasChanged(['sku', 'title', 'brand', 'is_active'])) {
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
            'is_active' => $this->is_active,
        ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('store');
    }

    public static function scopeWhereCountry(Builder $query, string $countryCode)
    {
        return $query->whereHas('store', function ($query) use ($countryCode) {
            $query->where('stores.country', $countryCode);
        });
    }

    public static function scopeDiscountedFirst(Builder $query): Builder
    {
        return $query->orderByRaw('
            CASE
                WHEN discount > 40 THEN 0
                WHEN discount BETWEEN 20 AND 39 THEN 1
                WHEN discount BETWEEN 1 AND 19 THEN 2
                ELSE 3
            END
        ');
    }

    public static function scopeOnlyRecentlyPriced(Builder $query): Builder
    {
        return $query->whereIsActive(true)->where('priced_at', '>=', now()->subDays(self::DAYS_OUTDATED));
    }

    public static function scopeOnlyDiscounted(Builder $query, int $discount = 20): Builder
    {
        return $query->whereIsActive(true)->where('discount', '>=', $discount);
    }

    public static function scopeWhereCategorySlug(Builder $query, mixed $slug): Builder
    {
        $ids = Category::whereSlug($slug)->pluck('id')->all();

        return $query->whereCategoryIds($ids);
    }

    public static function scopeWhereTaxonomySlug(Builder $query, string $slug): Builder
    {
        $taxonomyIds = Taxonomy::query()
            ->whereSlug($slug)
            ->select('id')
            ->pluck('id')
            ->unique()
            ->all();

        $categoryIds = DB::table('category_taxonomy')
            ->whereIn('taxonomy_id', $taxonomyIds)
            ->pluck('category_id')
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

    public function link(): string
    {
        return route('products.show', [$this->store->slug, $this->sku, $this->slug]);
    }

    public function storeLink(): string
    {
        return route('catalogs.index', [$this->store->slug]);
    }

    public function brandLink(): string
    {
        return route('catalogs.brand', [$this->store->slug, $this->brand_slug]);
    }

    public function categoryLink(): string
    {
        return route('catalogs.category', [$this->store->slug, $this->categories->last()->slug]);
    }

    public function categoryBrandLink(): string
    {
        return route('catalogs.category_brand', [$this->store->slug, $this->categories->last()->slug, $this->brand_slug]);
    }
}
