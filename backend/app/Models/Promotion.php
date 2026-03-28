<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $fillable = [
        'shop_id', 'title', 'description', 'type',
        'discount_percent', 'discount_amount', 'banner_image',
        'badge_text', 'badge_color', 'product_ids',
        'active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isRunning(): bool
    {
        if (!$this->active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return true;
    }
}
