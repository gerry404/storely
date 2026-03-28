<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformPromotion extends Model
{
    protected $fillable = [
        'name', 'description', 'type', 'plan', 'duration_days',
        'max_uses', 'used_count', 'promo_code', 'active',
        'starts_at', 'expires_at', 'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this promo can still be used.
     */
    public function isAvailable(): bool
    {
        if (!$this->active) return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;

        return true;
    }

    /**
     * Find the first available auto_register promo.
     */
    public static function findAutoRegisterPromo(): ?self
    {
        return static::where('type', 'auto_register')
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('max_uses')
                  ->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function remaining(): ?int
    {
        if ($this->max_uses === null) return null;
        return max(0, $this->max_uses - $this->used_count);
    }
}
