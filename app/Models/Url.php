<?php

namespace App\Models;

use App\Models\Enums\UrlCooldown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasFactory;

    public const COOLDOWN_1_DAY = 1;

    public const COOLDOWN_7_DAYS = 7;

    public const COOLDOWN_30_DAYS = 30;

    public $attributes = [
        'hits' => 0,
    ];

    protected $casts = [
        'crawled_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function proxied(): string
    {
        $proxy = config('descuento.crawler.proxy_url', '');

        return $proxy.$this->href;
    }

    public function hit(int $status, ?UrlCooldown $cooldown = null)
    {
        $cooldown = $cooldown ?? match ($status) {
            404 => UrlCooldown::SANITY_CHECK,
            default => UrlCooldown::MID_COST_PAGE,
        };

        $this->status = $status;
        $this->crawled_at = now();
        $this->scheduled_at = now()->addDays($cooldown->value);
        $this->hits = $this->hits + 1;
        $this->save();
    }

    public function follow(): bool
    {
        return config('app.env') == 'local'
            || ($this->scheduled_at && now()->isAfter($this->scheduled_at));
    }

    public static function scopeScheduled(Builder $builder, int $limit): Builder
    {
        return $builder->where('scheduled_at', '<', now())
            ->orderBy('scheduled_at')
            ->limit($limit);
    }
}
