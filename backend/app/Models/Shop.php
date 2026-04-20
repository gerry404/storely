<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'category', 'description',
        'phone', 'whatsapp', 'email', 'address', 'city',
        'open_hours', 'logo', 'banner', 'verified', 'active',
        'plan', 'customization', 'featured_products',
        'country', 'currency', 'featured_at', 'boost_expires_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'active' => 'boolean',
        'customization' => 'array',
        'featured_products' => 'array',
        'featured_at' => 'datetime',
        'boost_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function digitalSales(): HasMany
    {
        return $this->hasMany(DigitalSale::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'shop_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function isBoosted(): bool
    {
        return $this->boost_expires_at && $this->boost_expires_at->isFuture();
    }

    /**
     * Get the effective plan considering active subscription.
     */
    public function getEffectivePlan(): string
    {
        $sub = $this->subscription;
        if ($sub && $sub->isActive()) {
            return $sub->plan;
        }
        return $this->plan ?? 'free';
    }
}
