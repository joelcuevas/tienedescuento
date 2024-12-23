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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use HasPrices;

    protected $attributes = [
        'views' => 0,
    ];

    protected $casts = [
        'priced_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            $product->title = Str::limit($product->title, 250);
            $product->slug = Str::slug($product->title);

            $product->brand = Str::limit($product->brand, 250);
            $product->brand_slug = Str::slug($product->brand);
        });
    }

    protected function category(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->categories->first()->title,
        );
    }

    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn () => route('products.show', [$this->store->slug, $this->sku, $this->slug]),
        );
    }

    protected function storeLink(): Attribute
    {
        return Attribute::make(
            get: fn () => route('catalogs.store', [$this->store->slug]),
        );
    }

    protected function brandLink(): Attribute
    {
        return Attribute::make(
            get: fn () => route('catalogs.brand', [$this->store->slug, $this->brand_slug]),
        );
    }

    protected function categoryLink(): Attribute
    {
        return Attribute::make(
            get: fn () => route('catalogs.category', [$this->store->slug, $this->categories->first()->slug]),
        );
    }

    protected function categoryBrandLink(): Attribute
    {
        return Attribute::make(
            get: fn () => route('catalogs.category_brand', [
                $this->store->slug,
                $this->categories->first()->slug,
                $this->brand_slug,
            ]),
        );
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

    public function searchableAs(): string
    {
        return 'products_index';
    }

    public function isOutdated(): bool
    {
        return $this->priced_at < now()->subDays(3);
    }

    public static function scopeRecent(Builder $query): Builder
    {
        return $query->where('priced_at', '>=', now()->subDays(3));
    }

    public static function scopeWhereCategory(Builder $query, string $categorySlug, int $depth = 1): Builder
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

    public static function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereRaw("MATCH(brand, sku, title) AGAINST(? IN BOOLEAN MODE)", ["{$term}*"]);
    }

    public static function searchByUrl($url): Collection
    {
        $files = glob(app_path('Crawlers/*ProductCrawler.php'));

        foreach ($files as $file) {
            $className = basename($file, '.php');

            $fqcn = "App\\Crawlers\\$className";
            $crawler = new $fqcn($url);
            $product = $crawler->resolveProduct();

            if ($product) {
                return collect([$product]);
            }
        }

        return collect();
    }
}
