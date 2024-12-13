<?php

namespace App\Models;

use App\Models\Traits\Product\HasPrices;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use HasPrices;
    use Searchable;

    protected $casts = [
        'priced_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            $product->title = Str::limit($product->title, 250);
            $product->slug = Str::slug($product->title);
        });
    }

    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn () => route('products.show', [$this->store->slug, $this->sku, $this->slug]),
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

    #[SearchUsingFullText(['brand,sku,title'])]
    public function toSearchableArray(): array
    {
        return [
            'brand' => $this->brand,
            'sku' => $this->sku,
            'title' => $this->title,
        ];
    }
}
