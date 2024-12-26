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

    public function link(): string
    {
        return route('catalogs.store', [$this->slug]);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? 'https://placehold.co/400?text='.$this->name,
        );
    }

    public function thumbAspect()
    {
        return config('stores.'.$this->country.'.'.$this->slug.'.thumb-aspect', 'portrait');
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
