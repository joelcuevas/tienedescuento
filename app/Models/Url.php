<?php

namespace App\Models;

use App\Models\Enums\UrlCooldown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasFactory;

    public const SAFE_STATUSES = [200, 304, 404];

    public $attributes = [
        'hits' => 0,
        'streak' => 0,
    ];

    protected $casts = [
        'crawled_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Url $url) {
            $url->hash = sha1($url->href);
        });
    }

    public function proxied(): string
    {
        $proxy = config('descuento.crawler.proxy_url', '');

        return $proxy.$this->href;
    }

    public function queue(): string
    {
        $slug = str_replace('.', '-', $this->domain);

        return config("descuento.crawler.domains.{$slug}.queue", 'default');
    }

    public function reserve(): void
    {
        $this->reserved_at = now();
        $this->save();
    }

    public function hit(int $status, ?UrlCooldown $cooldown = null)
    {
        $cooldown = $cooldown ?? match ($status) {
            404 => UrlCooldown::SANITY_CHECK,
            503 => UrlCooldown::TOMORROW,
            default => UrlCooldown::MID_COST_PAGE,
        };

        $repeatingStatus = ! in_array($status, static::SAFE_STATUSES) && $status == $this->status;
        $streak = $repeatingStatus ? $this->streak + 1 : 1;
        $scheduledAt = $cooldown->value * ($streak % 5);

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
        return $builder->limit($limit)
            ->where('scheduled_at', '<', now())
            ->where(function (Builder $q) {
                $q->whereNull('reserved_at')->orWhere('reserved_at', '<', now()->subHours(3));
            })
            ->orderBy('scheduled_at');
    }
}
