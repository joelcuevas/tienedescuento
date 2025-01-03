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
    use Searchable;
    use HasFactory;
    use HasPrices;

    const DAYS_OUTDATED = 3;

    protected $attributes = [
        'views' => 0,
    ];

    protected $casts = [
        'priced_at' => 'datetime',
        'priced_date' => 'date',
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

    public function isOutdated(): bool
    {
        return $this->priced_at < now()->subDays(self::DAYS_OUTDATED);
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

    public static function scopeRecent(Builder $query): Builder
    {
        return $query->where('priced_at', '>=', now()->subDays(self::DAYS_OUTDATED));
    }

    public static function scopeWhereCategory(Builder $query, string $categorySlug, int $depth = 0): Builder
    {
        $categoryIds = Category::whereSlugTree($categorySlug)->pluck('id')->all();

        return $query
            ->joinSub(
                DB::table('category_product')
                    ->select('product_id')
                    ->distinct()
                    ->forceIndex('category_product_category_id_product_id_index')
                    ->whereIn('category_id', $categoryIds),
                'cp',
                'products.id',
                '=',
                'cp.product_id'
            );
    }
}
