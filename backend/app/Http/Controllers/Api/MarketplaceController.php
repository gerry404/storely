<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function explore(Request $request)
    {
        $query = Shop::where('active', true)
            ->withCount('products', 'reviews', 'orders')
            ->withAvg('reviews', 'rating')
            ->with('badges');

        // Search
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // Filter by city
        if ($city = $request->get('city')) {
            $query->where('city', $city);
        }

        // Filter by country
        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        // Sorting: boosted shops first, then by relevance
        $query->orderByRaw('CASE WHEN boost_expires_at > NOW() THEN 0 ELSE 1 END')
              ->orderByDesc('featured_at')
              ->orderByDesc('orders_count');

        $shops = $query->paginate(24);

        // Add effective plan to each shop
        $shops->getCollection()->transform(function ($shop) {
            $shop->effective_plan = $shop->getEffectivePlan();
            $shop->is_boosted = $shop->isBoosted();
            return $shop;
        });

        return response()->json($shops);
    }

    public function featured()
    {
        $shops = Shop::where('active', true)
            ->whereNotNull('featured_at')
            ->withCount('products', 'orders')
            ->withAvg('reviews', 'rating')
            ->with('badges')
            ->orderByDesc('featured_at')
            ->limit(12)
            ->get()
            ->map(function ($shop) {
                $shop->effective_plan = $shop->getEffectivePlan();
                return $shop;
            });

        return response()->json($shops);
    }

    public function cities(Request $request)
    {
        $country = $request->get('country', 'CM');

        $cities = Shop::where('active', true)
            ->where('country', $country)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->selectRaw('city, COUNT(*) as shop_count')
            ->groupBy('city')
            ->orderByDesc('shop_count')
            ->limit(50)
            ->get();

        return response()->json($cities);
    }

    public function categories()
    {
        $categories = Shop::where('active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as shop_count')
            ->groupBy('category')
            ->orderByDesc('shop_count')
            ->get();

        return response()->json($categories);
    }
}
