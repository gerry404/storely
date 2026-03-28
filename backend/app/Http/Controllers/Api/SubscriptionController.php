<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Subscription;
use App\Services\PlanService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Public: get all plans info for pricing page.
     */
    public function plans()
    {
        return response()->json(PlanService::getAllPlans());
    }

    /**
     * Get current subscription details.
     */
    public function current(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->with('subscription')->firstOrFail();

        $sub = $shop->subscription;
        $effectivePlan = $shop->getEffectivePlan();

        return response()->json([
            'subscription' => $sub && $sub->isActive() ? $sub : null,
            'effective_plan' => $effectivePlan,
            'plan_info' => PlanService::getPlanInfo($shop),
        ]);
    }

    /**
     * Subscribe to a plan (or upgrade/downgrade).
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:starter,pro',
            'billing_cycle' => 'required|in:monthly,annual',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
            'apply_promo' => 'nullable|boolean',
        ]);

        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        $plan = $request->plan;
        $cycle = $request->billing_cycle;
        $applyPromo = $request->boolean('apply_promo');

        // Calculate pricing
        $pricing = PlanService::calculatePrice($plan, $cycle, $applyPromo);
        $amount = $pricing['amount'];

        // Determine subscription duration
        if ($cycle === 'annual') {
            $expiresAt = now()->addYear();
        } else {
            $expiresAt = now()->addMonth();
        }

        // Cancel existing active subscription
        $existingSub = $shop->subscription;
        if ($existingSub && $existingSub->isActive()) {
            $existingSub->update(['status' => 'cancelled']);
        }

        // Create new subscription
        $subscription = Subscription::create([
            'shop_id' => $shop->id,
            'plan' => $plan,
            'billing_cycle' => $cycle,
            'amount' => $amount,
            'original_amount' => $pricing['original_amount'] ?? null,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'promo_code' => $applyPromo ? 'LAUNCH63' : null,
            'promo_months_remaining' => $applyPromo ? PlanService::LAUNCH_PROMO['months'] - 1 : null,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // Update shop plan
        $shop->update(['plan' => $plan]);

        return response()->json([
            'subscription' => $subscription,
            'plan_info' => PlanService::getPlanInfo($shop->fresh()),
        ], 201);
    }

    /**
     * Cancel current subscription.
     */
    public function cancel(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        $sub = $shop->subscription;

        if (!$sub || !$sub->isActive()) {
            return response()->json(['message' => 'Aucun abonnement actif.'], 404);
        }

        // Subscription stays active until expiry, but won't renew
        $sub->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Abonnement annulé. Vous conservez vos avantages jusqu\'au ' . $sub->expires_at->format('d/m/Y') . '.',
            'expires_at' => $sub->expires_at,
        ]);
    }
}
