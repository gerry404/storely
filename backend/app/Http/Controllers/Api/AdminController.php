<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Badge;
use App\Models\DigitalSale;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformPromotion;
use App\Models\PlatformSetting;
use App\Models\Product;
use App\Models\Referral;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ─── Dashboard Overview ─────────────────────────────

    public function dashboard()
    {
        $now = now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'total_shops' => Shop::count(),
                'active_shops' => Shop::where('active', true)->count(),
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'orders_30d' => Order::where('created_at', '>=', $thirtyDaysAgo)->count(),
                'total_revenue' => Payment::where('status', 'successful')->sum('amount'),
                'revenue_30d' => Payment::where('status', 'successful')
                    ->where('paid_at', '>=', $thirtyDaysAgo)->sum('amount'),
                'active_subscriptions' => Subscription::where('status', 'active')
                    ->where('expires_at', '>', $now)->count(),
                'total_referrals' => Referral::count(),
                'digital_commissions' => DigitalSale::sum('commission_amount'),
            ],
            'plan_distribution' => [
                'free' => Shop::where('plan', 'free')->orWhereNull('plan')->count(),
                'starter' => Shop::where('plan', 'starter')->count(),
                'pro' => Shop::where('plan', 'pro')->count(),
            ],
            'country_distribution' => Shop::selectRaw('COALESCE(country, "CM") as country, COUNT(*) as count')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(20)
                ->get(),
            'recent_registrations' => User::latest()->limit(10)->get(['id', 'name', 'email', 'created_at']),
            'recent_payments' => Payment::where('status', 'successful')
                ->with('user:id,name,email', 'shop:id,name,slug')
                ->latest('paid_at')->limit(10)->get(),
        ]);
    }

    // ─── Users Management ───────────────────────────────

    public function users(Request $request)
    {
        $query = User::with('shop:id,user_id,name,slug,plan,active');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        return response()->json($query->latest()->paginate(30));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'role' => 'nullable|in:user,admin,superadmin',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
        ]);

        $changes = [];
        if ($request->has('role') && $request->role !== $user->role) {
            $changes['role'] = ['from' => $user->role, 'to' => $request->role];
            $user->role = $request->role;
        }
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        $user->save();

        if (!empty($changes)) {
            $this->log($request, 'update_user_role', 'user', $user->id, $changes);
        }

        return response()->json($user->load('shop:id,user_id,name,slug,plan'));
    }

    public function deleteUser(Request $request, User $user)
    {
        if ($user->role === 'superadmin') {
            return response()->json(['message' => 'Impossible de supprimer un superadmin.'], 403);
        }

        $this->log($request, 'delete_user', 'user', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    // ─── Shops Management ───────────────────────────────

    public function shops(Request $request)
    {
        $query = Shop::with('user:id,name,email')
            ->withCount('products', 'orders', 'reviews')
            ->withAvg('reviews', 'rating');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->get('plan')) {
            $query->where('plan', $plan);
        }

        if ($request->get('active') !== null) {
            $query->where('active', $request->boolean('active'));
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'orders' => $query->orderByDesc('orders_count'),
            'products' => $query->orderByDesc('products_count'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return response()->json($query->paginate(30));
    }

    public function updateShop(Request $request, Shop $shop)
    {
        $request->validate([
            'plan' => 'nullable|in:free,starter,pro',
            'active' => 'nullable|boolean',
            'verified' => 'nullable|boolean',
            'featured_at' => 'nullable|date',
            'boost_expires_at' => 'nullable|date',
        ]);

        $changes = [];

        if ($request->has('plan') && $request->plan !== $shop->plan) {
            $changes['plan'] = ['from' => $shop->plan, 'to' => $request->plan];
            $shop->plan = $request->plan;
        }
        if ($request->has('active')) {
            $changes['active'] = ['from' => $shop->active, 'to' => $request->boolean('active')];
            $shop->active = $request->boolean('active');
        }
        if ($request->has('verified')) {
            $changes['verified'] = ['from' => $shop->verified, 'to' => $request->boolean('verified')];
            $shop->verified = $request->boolean('verified');
        }
        if ($request->has('featured_at')) {
            $shop->featured_at = $request->featured_at;
        }
        if ($request->has('boost_expires_at')) {
            $shop->boost_expires_at = $request->boost_expires_at;
        }

        $shop->save();

        $this->log($request, 'update_shop', 'shop', $shop->id, $changes);

        return response()->json($shop->load('user:id,name,email'));
    }

    public function deleteShop(Request $request, Shop $shop)
    {
        $this->log($request, 'delete_shop', 'shop', $shop->id, [
            'name' => $shop->name,
            'slug' => $shop->slug,
        ]);

        $shop->delete();

        return response()->json(['message' => 'Boutique supprimée.']);
    }

    // ─── Orders Management ──────────────────────────────

    public function orders(Request $request)
    {
        $query = Order::with('shop:id,name,slug', 'product:id,name,price');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($shopId = $request->get('shop_id')) {
            $query->where('shop_id', $shopId);
        }

        return response()->json($query->latest()->paginate(30));
    }

    // ─── Payments / Revenue ─────────────────────────────

    public function payments(Request $request)
    {
        $query = Payment::with('user:id,name,email', 'shop:id,name,slug');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        return response()->json($query->latest()->paginate(30));
    }

    public function revenueReport(Request $request)
    {
        $days = $request->get('days', 30);
        $from = now()->subDays($days);

        $daily = Payment::where('status', 'successful')
            ->where('paid_at', '>=', $from)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byType = Payment::where('status', 'successful')
            ->where('paid_at', '>=', $from)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $byMethod = Payment::where('status', 'successful')
            ->where('paid_at', '>=', $from)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'period_days' => $days,
            'total' => Payment::where('status', 'successful')->where('paid_at', '>=', $from)->sum('amount'),
            'count' => Payment::where('status', 'successful')->where('paid_at', '>=', $from)->count(),
            'daily' => $daily,
            'by_type' => $byType,
            'by_method' => $byMethod,
            'digital_commissions' => DigitalSale::where('created_at', '>=', $from)->sum('commission_amount'),
        ]);
    }

    // ─── Subscriptions Management ───────────────────────

    public function subscriptions(Request $request)
    {
        $query = Subscription::with('shop:id,name,slug,plan');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($plan = $request->get('plan')) {
            $query->where('plan', $plan);
        }

        return response()->json($query->latest()->paginate(30));
    }

    // ─── Badges Management ──────────────────────────────

    public function badges()
    {
        return response()->json(
            Badge::withCount('shops')->orderBy('sort_order')->get()
        );
    }

    public function awardBadge(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'badge_id' => 'required|exists:badges,id',
        ]);

        $shop = Shop::findOrFail($request->shop_id);

        if ($shop->badges()->where('badge_id', $request->badge_id)->exists()) {
            return response()->json(['message' => 'Badge déjà attribué.'], 422);
        }

        $shop->badges()->attach($request->badge_id, ['earned_at' => now()]);

        $this->log($request, 'award_badge', 'shop', $shop->id, [
            'badge_id' => $request->badge_id,
        ]);

        return response()->json(['message' => 'Badge attribué.']);
    }

    public function revokeBadge(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'badge_id' => 'required|exists:badges,id',
        ]);

        Shop::findOrFail($request->shop_id)->badges()->detach($request->badge_id);

        $this->log($request, 'revoke_badge', 'shop', $request->shop_id, [
            'badge_id' => $request->badge_id,
        ]);

        return response()->json(['message' => 'Badge retiré.']);
    }

    // ─── Platform Settings ──────────────────────────────

    public function settings()
    {
        return response()->json(PlatformSetting::all());
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable',
            'type' => 'nullable|in:string,boolean,integer,json',
        ]);

        PlatformSetting::set(
            $request->key,
            $request->value,
            $request->type ?? 'string'
        );

        $this->log($request, 'update_setting', 'setting', null, [
            'key' => $request->key,
        ]);

        return response()->json(['message' => 'Paramètre mis à jour.']);
    }

    // ─── Referrals Management ───────────────────────────

    public function referrals(Request $request)
    {
        $query = Referral::with('referrer:id,name,email', 'referred:id,name,email');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->paginate(30));
    }

    public function rewardReferral(Request $request, Referral $referral)
    {
        if ($referral->status !== 'qualified') {
            return response()->json(['message' => 'Ce parrainage n\'est pas qualifié.'], 422);
        }

        $referral->update([
            'status' => 'rewarded',
            'rewarded_at' => now(),
        ]);

        $this->log($request, 'reward_referral', 'referral', $referral->id);

        return response()->json(['message' => 'Parrainage récompensé.']);
    }

    // ─── Activity Logs ──────────────────────────────────

    public function logs(Request $request)
    {
        $query = AdminLog::with('admin:id,name,email');

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        return response()->json($query->latest()->paginate(50));
    }

    // ─── Platform Promotions ─────────────────────────────

    public function promotions()
    {
        return response()->json(
            PlatformPromotion::with('creator:id,name')
                ->latest()
                ->get()
                ->map(fn($p) => array_merge($p->toArray(), [
                    'remaining' => $p->remaining(),
                    'is_available' => $p->isAvailable(),
                ]))
        );
    }

    public function createPromotion(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:auto_register,manual,code',
            'plan' => 'required|in:starter,pro',
            'duration_days' => 'required|integer|min:1|max:365',
            'max_uses' => 'nullable|integer|min:1',
            'promo_code' => 'nullable|string|max:50|unique:platform_promotions,promo_code',
            'active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        $promo = PlatformPromotion::create([
            ...$request->only([
                'name', 'description', 'type', 'plan', 'duration_days',
                'max_uses', 'promo_code', 'active', 'starts_at', 'expires_at',
            ]),
            'created_by' => $request->user()->id,
        ]);

        $this->log($request, 'create_promotion', 'promotion', $promo->id, [
            'name' => $promo->name,
            'plan' => $promo->plan,
            'max_uses' => $promo->max_uses,
        ]);

        return response()->json($promo, 201);
    }

    public function updatePromotion(Request $request, PlatformPromotion $promotion)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'plan' => 'nullable|in:starter,pro',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'max_uses' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $promotion->update($request->only([
            'name', 'description', 'plan', 'duration_days',
            'max_uses', 'active', 'starts_at', 'expires_at',
        ]));

        $this->log($request, 'update_promotion', 'promotion', $promotion->id);

        return response()->json(array_merge($promotion->fresh()->toArray(), [
            'remaining' => $promotion->remaining(),
            'is_available' => $promotion->isAvailable(),
        ]));
    }

    public function deletePromotion(Request $request, PlatformPromotion $promotion)
    {
        $this->log($request, 'delete_promotion', 'promotion', $promotion->id, [
            'name' => $promotion->name,
        ]);

        $promotion->delete();

        return response()->json(['message' => 'Promotion supprimée.']);
    }

    // ─── Helper ─────────────────────────────────────────

    private function log(Request $request, string $action, ?string $targetType = null, ?int $targetId = null, ?array $details = null): void
    {
        AdminLog::create([
            'admin_id' => $request->user()->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
        ]);
    }
}
