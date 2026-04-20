<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = [
        'shop_id', 'name', 'description', 'price',
        'estimated_hours', 'is_default', 'active', 'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'active' => 'boolean',
        'price' => 'integer',
        'estimated_hours' => 'integer',
        'sort_order' => 'integer',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
