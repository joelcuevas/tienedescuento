<?php

namespace App\Models;

use App\Models\Traits\Url\HasCrawlers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasCrawlers;
    use HasFactory;

    public $attributes = [
        'hits' => 0,
        'streak' => 0,
    ];

    protected $casts = [
        'crawled_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    public static function scopeScheduled(Builder $builder, int $limit): Builder
    {
        return $builder
            ->where('scheduled_at', '<', now())
            ->where(fn ($q) => $q->whereNull('reserved_at')->orWhere('reserved_at', '<', now()->subHours(3)))
            ->limit($limit)
            ->orderBy('scheduled_at');
    }
}
