<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\PlanService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function show(string $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('active', true)
            ->with(['products' => function ($q) {
                $q->where('active', true)->orderBy('sort_order');
            }, 'reviews' => function ($q) {
                $q->latest()->take(10);
            }, 'promotions' => function ($q) {
                $q->where('active', true)
                  ->where(function ($q2) {
                      $q2->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                  });
            }])
            ->firstOrFail();

        $shop->increment('views_count');

        // Include plan for frontend feature gating
        $shop->effective_plan = $shop->getEffectivePlan();

        return response()->json($shop);
    }

    public function update(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'description' => 'sometimes|string|max:1000',
            'phone' => 'sometimes|string',
            'whatsapp' => 'sometimes|string',
            'email' => 'sometimes|nullable|email|max:255',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'open_hours' => 'sometimes|string|max:100',
            'customization' => 'sometimes|string|max:10000',
            'featured_products' => 'sometimes|string|max:5000',
        ]);

        $shop->update($request->only([
            'name', 'category', 'description', 'phone',
            'whatsapp', 'email', 'address', 'city', 'open_hours',
            'customization', 'featured_products',
        ]));

        return response()->json($shop);
    }

    public function stats(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        $plan = PlanService::getEffectivePlan($shop);

        $stats = [
            'views_today' => $shop->views_count,
            'total_products' => $shop->products()->count(),
            'active_products' => $shop->products()->where('active', true)->count(),
            'total_orders' => $shop->orders()->count(),
            'new_orders' => $shop->orders()->where('status', 'new')->count(),
            'revenue_month' => $shop->orders()
                ->where('status', '!=', 'cancelled')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
            'paid_revenue_month' => $shop->orders()
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('seller_amount'),
            'pending_revenue_month' => $shop->orders()
                ->where('payment_status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
            'paid_orders_count' => $shop->orders()
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'plan_info' => PlanService::getPlanInfo($shop),
        ];

        // Preorder stats
        $stats['preorder_count'] = $shop->orders()->where('is_preorder', true)
            ->where('status', '!=', 'cancelled')->count();

        // Digital sales stats
        if (PlanService::hasFeature($shop, 'digital_products')) {
            $stats['digital_revenue_month'] = $shop->digitalSales()
                ->whereMonth('created_at', now()->month)
                ->sum('seller_amount');
            $stats['digital_commissions_month'] = $shop->digitalSales()
                ->whereMonth('created_at', now()->month)
                ->sum('commission_amount');
        }

        // Low stock alerts (Pro only)
        if (PlanService::hasFeature($shop, 'stock_management')) {
            $stats['low_stock_products'] = $shop->products()
                ->where('active', true)
                ->where('product_type', 'physical')
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->select('id', 'name', 'stock')
                ->get();
        }

        return response()->json($stats);
    }

    public function uploadBanner(Request $request)
    {
        $request->validate(['banner' => 'required|image|max:5120']);
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        $path = $request->file('banner')->store('shops/banners', 'public');
        $shop->update(['banner' => $path]);
        return response()->json(['banner' => $path]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        $path = $request->file('logo')->store('shops/logos', 'public');
        $shop->update(['logo' => $path]);

        return response()->json(['logo' => $path]);
    }
}
