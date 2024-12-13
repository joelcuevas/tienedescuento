<?php

namespace App\Models;

use App\Models\Traits\Url\HasCrawlers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

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

    public function proxied(): string
    {
        $proxy = config('descuento.crawler.proxy_url', '');

        return $proxy.$this->href;
    }

    public function hit(int $status, int $cooldown)
    {
        // add 2^($streak % 7) days of cooldown if a streak of status != 200 occurs
        // example: streak(7) = cumulative sum of 2 + 4 + 8 + 16 + 32 + 64 ≈ 4 months
        // after this, $streak % 7 causes the cycle to restart at 2 days again
        $streak = ($status == $this->status) ? $this->streak + 1 : 1;
        $penalty = ($status == Response::HTTP_OK) ? 0 : (2 ** ($streak % 7));
        $scheduledAt = $cooldown + $penalty;

        $this->streak = $streak;
        $this->status = $status;
        $this->crawled_at = now();
        $this->scheduled_at = now()->addDays($scheduledAt);
        $this->reserved_at = null;
        $this->hits = $this->hits + 1;
        $this->save();
    }

    public static function scopeScheduled(Builder $builder, int $limit): Builder
    {
        return $builder
            ->where('scheduled_at', '<', now())
            ->where(fn ($q) => $q->whereNull('reserved_at')->orWhere('reserved_at', '<', now()->subHours(3)))
            ->limit($limit)
            ->orderBy('scheduled_at');
    }
}
