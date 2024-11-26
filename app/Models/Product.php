<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'priced_at' => 'datetime',
        ];
    }

    public function addPrice(float $price, string $source, ?Carbon $pricedAt = null): void
    {
        $price = $this->prices()->create([
            'price' => $price,
            'priced_at' => $pricedAt ?? now(),
            'source' => $source,
        ]);

        $prices = $this->fresh()->prices;
        $latest = $prices->sortByDesc('priced_at')->first();
        $this->latest_price = $latest->price;
        $this->priced_at = $latest->priced_at;
        $this->minimum_price = $prices->min('price');
        $this->maximum_price = $prices->max('price');
        $this->regular_price = min($prices->mode('price'));
        $this->save();
    }

    protected function latestPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->latest_price, 2),
        );
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
