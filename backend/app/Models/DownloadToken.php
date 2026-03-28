<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DownloadToken extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'token',
        'downloads_remaining', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DownloadToken $dt) {
            if (empty($dt->token)) {
                $dt->token = Str::random(64);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isValid(): bool
    {
        return $this->downloads_remaining > 0 && $this->expires_at->isFuture();
    }

    public function consumeDownload(): bool
    {
        if (!$this->isValid()) return false;
        $this->decrement('downloads_remaining');
        return true;
    }
}
