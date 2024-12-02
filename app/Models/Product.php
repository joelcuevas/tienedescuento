<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

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

    public function link(): string
    {
        return route('products.show', [$this->store->slug, $this->sku, $this->slug]);
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
}
