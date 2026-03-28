<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'slug', 'name', 'name_en', 'description',
        'icon', 'color', 'criteria_type', 'criteria_value', 'sort_order',
    ];

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
