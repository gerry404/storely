<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'shop_id', 'name', 'slug', 'description', 'long_description',
        'price', 'compare_price', 'stock', 'active', 'images',
        'category', 'subcategory', 'sort_order',
        'product_type', 'is_preorder', 'preorder_available_at',
        'preorder_deposit_percent', 'digital_file_path', 'digital_download_limit',
    ];

    protected $casts = [
        'images' => 'array',
        'active' => 'boolean',
        'is_preorder' => 'boolean',
        'preorder_available_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $base = Str::slug($product->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $product->slug = $slug;
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isDigital(): bool
    {
        return $this->product_type === 'digital';
    }
}
