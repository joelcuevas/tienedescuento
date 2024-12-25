<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $attributes = [
        'views' => 0,
    ];

    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn () => route('catalogs.store', [$this->slug]),
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? 'https://placehold.co/400?text='.$this->name,
        );
    }

    public function getImageFit()
    {
        return config('stores.'.$this->country.'.'.$this->slug.'.image-fit', 'object-cover');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
