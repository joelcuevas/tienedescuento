<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    protected $casts = [
        'priced_at' => 'datetime',
        'priced_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Price $price) {
            // if no date is set, set to now
            $price->priced_at = $price->priced_at ?? now();
            $price->priced_date = $price->priced_at;

            // if there's already a price for the date, keep the min
            $existing = $price->product->prices()
                ->whereSource($price->source)
                ->wherePricedDate($price->priced_date)
                ->first();

            if ($existing) {
                $existing->price = min($existing->price, $price->price);
                $existing->save();

                return false;
            }
        });

        static::created(function (Price $price) {
            $price->product->updatePrices();
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
