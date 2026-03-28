<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Shop;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        return response()->json(Badge::orderBy('sort_order')->get());
    }

    public function shopBadges(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        return response()->json([
            'earned' => $shop->badges()->orderBy('sort_order')->get(),
            'available' => Badge::orderBy('sort_order')->get(),
        ]);
    }

    public function checkAndAward(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)
            ->withCount('orders', 'reviews')
            ->withAvg('reviews', 'rating')
            ->firstOrFail();

        $badges = Badge::all();
        $awarded = [];

        foreach ($badges as $badge) {
            if ($shop->badges()->where('badge_id', $badge->id)->exists()) {
                continue;
            }

            $earned = match ($badge->criteria_type) {
                'orders_count' => $shop->orders_count >= $badge->criteria_value,
                'reviews_avg' => $shop->reviews_avg_rating && ($shop->reviews_avg_rating * 10) >= $badge->criteria_value,
                'monthly_orders' => $shop->orders()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count() >= $badge->criteria_value,
                'account_age_months' => $shop->created_at->diffInMonths(now()) >= $badge->criteria_value,
                'digital_sales_count' => $shop->digitalSales()->count() >= $badge->criteria_value,
                default => false,
            };

            if ($earned) {
                $shop->badges()->attach($badge->id, ['earned_at' => now()]);
                $awarded[] = $badge;
            }
        }

        return response()->json([
            'newly_awarded' => $awarded,
            'total_badges' => $shop->badges()->count(),
        ]);
    }
}
