<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'shop_id', 'product_id', 'customer_name', 'customer_phone',
        'customer_email', 'quantity', 'total', 'status', 'note',
        'is_preorder', 'deposit_amount',
        'payment_status', 'payment_method', 'payment_reference',
        'commission_amount', 'seller_amount', 'paid_at',
        'delivery_zone_id', 'delivery_fee', 'delivery_address',
    ];

    protected $casts = [
        'is_preorder' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function digitalSale(): HasOne
    {
        return $this->hasOne(DigitalSale::class);
    }

    public function downloadToken(): HasOne
    {
        return $this->hasOne(DownloadToken::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
