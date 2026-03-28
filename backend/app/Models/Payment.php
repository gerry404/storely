<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'shop_id', 'type', 'flw_ref', 'flw_tx_ref',
        'amount', 'currency', 'status', 'payment_method',
        'metadata', 'flw_response', 'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'flw_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }
}
