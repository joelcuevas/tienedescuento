<?php

namespace App\Models;

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

    protected function latestPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->latest_price, 2),
        );
    }

    protected function minumimPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->minumim_price, 2),
        );
    }

    protected function maximumPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->maximum_price, 2),
        );
    }

    protected function regularPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->regular_price, 2),
        );
    }

    public function updatePrices(): void
    {
        $prices = $this->prices;
        $latest = $prices->sortByDesc('priced_at')->first();

        $this->latest_price = $latest->price;
        $this->priced_at = $latest->priced_at;
        $this->minimum_price = $prices->min('price');
        $this->maximum_price = $prices->max('price');

        $pricesValues = $this->prices->pluck('price')->sort();
        $threshold = 0.10;

        $clusters = collect();
        $currentCluster = collect([$pricesValues->first()]);

        for ($i = 1; $i < $pricesValues->count(); $i++) {
            $previousPrice = $pricesValues->values()[$i - 1];
            $currentPrice = $pricesValues->values()[$i];

            if (abs($currentPrice - $previousPrice) / $previousPrice <= $threshold) {
                $currentCluster->push($currentPrice);
            } else {
                $clusters->push($currentCluster);
                $currentCluster = collect([$currentPrice]);
            }
        }

        $clusters->push($currentCluster);
        $largestCluster = $clusters->sortByDesc(fn ($cluster) => $cluster->count())->first();
        $regularPrice = $largestCluster->values()[intdiv($largestCluster->count() - 1, 2)];

        $this->regular_price = $regularPrice;
        $this->discount = (($this->regular_price - $latest->price) / $this->regular_price) * 100;

        $this->save();
    }

    public function findRegularPrice()
    {
        $prices = $this->prices->pluck('price');
        $prices = $prices->sort();
        $threshold = 0.20;

        $clusters = collect();
        $currentCluster = collect([$prices->first()]);

        for ($i = 1; $i < $prices->count(); $i++) {
            $previousPrice = $prices->values()[$i - 1];
            $currentPrice = $prices->values()[$i];

            if (abs($currentPrice - $previousPrice) / $previousPrice <= $threshold) {
                $currentCluster->push($currentPrice);
            } else {
                $clusters->push($currentCluster);
                $currentCluster = collect([$currentPrice]);
            }
        }

        $clusters->push($currentCluster);
        $largestCluster = $clusters->sortByDesc(fn ($cluster) => $cluster->count())->first();
        $regularPrice = $largestCluster->values()[intdiv($largestCluster->count() - 1, 2)];

        return $regularPrice;
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
            $product = $crawler->exists();

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
