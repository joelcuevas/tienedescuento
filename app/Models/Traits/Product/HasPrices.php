<?php

namespace App\Models\Traits\Product;

use App\Pricers\MidTermDropPricer;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasPrices
{
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

    protected function regularPriceLowerFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->regular_price_lower, 2),
        );
    }

    protected function regularPriceUpperFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '$'.number_format($this->regular_price_upper, 2),
        );
    }

    public function hasBestPrice(): bool
    {
        return $this->latest_price <= $this->minimum_price;
    }

    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    public function updatePrices(): bool
    {
        return (new MidTermDropPricer($this))->price();
    }
}
