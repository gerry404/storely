<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\Shop;
use App\Models\Subscription;
use App\Services\FlutterwaveService;
use App\Services\PlanService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Initialize a subscription payment via Flutterwave.
     */
    public function initSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:starter,pro',
            'billing_cycle' => 'required|in:monthly,annual',
            'apply_promo' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        $plan = $request->plan;
        $cycle = $request->billing_cycle;
        $applyPromo = $request->boolean('apply_promo');

        $pricing = PlanService::calculatePrice($plan, $cycle, $applyPromo);
        $amount = $cycle === 'annual' ? $pricing['total'] : $pricing['amount'];

        $flw = new FlutterwaveService();
        $result = $flw->initializePayment([
            'amount' => $amount,
            'currency' => $shop->currency ?? 'XAF',
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $shop->phone,
            'description' => "Abonnement Storely {$plan} ({$cycle})",
            'meta' => [
                'type' => 'subscription',
                'plan' => $plan,
                'billing_cycle' => $cycle,
                'apply_promo' => $applyPromo,
                'shop_id' => $shop->id,
                'user_id' => $user->id,
            ],
        ]);

        if (!$result['success']) {
            return response()->json(['message' => $result['message'] ?? 'Erreur de paiement'], 500);
        }

        // Create pending payment record
        Payment::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'type' => 'subscription',
            'flw_tx_ref' => $result['tx_ref'],
            'amount' => $amount,
            'currency' => $shop->currency ?? 'XAF',
            'status' => 'pending',
            'metadata' => [
                'plan' => $plan,
                'billing_cycle' => $cycle,
                'apply_promo' => $applyPromo,
                'monthly_amount' => $pricing['amount'],
                'original_amount' => $pricing['original_amount'] ?? null,
            ],
        ]);

        return response()->json([
            'payment_link' => $result['payment_link'],
            'tx_ref' => $result['tx_ref'],
        ]);
    }

    /**
     * Initialize an order payment via Flutterwave (public, no auth needed).
     */
    public function initOrderPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('shop')->findOrFail($request->order_id);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Cette commande a déjà été payée.'], 400);
        }

        $shop = $order->shop;
        $amount = $order->is_preorder && $order->deposit_amount ? $order->deposit_amount : $order->total;

        $flw = new FlutterwaveService();
        $result = $flw->initializePayment([
            'amount' => $amount,
            'currency' => $shop->currency ?? 'XAF',
            'email' => $order->customer_email ?: "customer-{$order->id}@storely.app",
            'name' => $order->customer_name,
            'phone' => $order->customer_phone,
            'redirect_url' => config('app.frontend_url', 'http://localhost:5173') . '/payment/order-callback',
            'description' => "Commande #{$order->id} - {$shop->name}",
            'meta' => [
                'type' => 'order',
                'order_id' => $order->id,
                'shop_id' => $shop->id,
            ],
        ]);

        if (!$result['success']) {
            return response()->json(['message' => $result['message'] ?? 'Erreur de paiement'], 500);
        }

        // Create pending payment record
        Payment::create([
            'user_id' => $shop->user_id,
            'shop_id' => $shop->id,
            'type' => 'order',
            'flw_tx_ref' => $result['tx_ref'],
            'amount' => $amount,
            'currency' => $shop->currency ?? 'XAF',
            'status' => 'pending',
            'metadata' => [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
            ],
        ]);

        $order->update([
            'payment_status' => 'pending',
            'payment_reference' => $result['tx_ref'],
        ]);

        return response()->json([
            'payment_link' => $result['payment_link'],
            'tx_ref' => $result['tx_ref'],
            'public_key' => $flw->getPublicKey(),
            'amount' => $amount,
            'currency' => $shop->currency ?? 'XAF',
            'customer_email' => $order->customer_email ?: "customer-{$order->id}@storely.app",
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'order_id' => $order->id,
        ]);
    }

    /**
     * Verify payment after Flutterwave redirect callback.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'tx_ref' => 'required|string',
        ]);

        $payment = Payment::where('flw_tx_ref', $request->tx_ref)->firstOrFail();

        if ($payment->status === 'successful') {
            return response()->json([
                'status' => 'already_processed',
                'message' => 'Ce paiement a déjà été traité.',
                'payment' => $payment,
            ]);
        }

        $flw = new FlutterwaveService();
        $verification = $flw->verifyTransaction($request->transaction_id);

        if (!$verification['success']) {
            $payment->update(['status' => 'failed']);
            return response()->json(['message' => 'Vérification du paiement échouée.'], 400);
        }

        // Verify amount matches
        if ($verification['amount'] < $payment->amount) {
            $payment->update([
                'status' => 'failed',
                'flw_response' => $verification['data'] ?? null,
            ]);
            return response()->json(['message' => 'Montant incorrect.'], 400);
        }

        if ($verification['status'] !== 'successful') {
            $payment->update([
                'status' => 'failed',
                'flw_ref' => $verification['flw_ref'] ?? null,
                'flw_response' => $verification['data'] ?? null,
            ]);
            return response()->json(['message' => 'Le paiement n\'a pas abouti.'], 400);
        }

        // Payment successful — activate
        $payment->update([
            'status' => 'successful',
            'flw_ref' => $verification['flw_ref'],
            'payment_method' => $verification['payment_type'],
            'flw_response' => $verification['data'],
            'paid_at' => now(),
        ]);

        $result = $this->processSuccessfulPayment($payment);

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement vérifié et activé !',
            'payment' => $payment->fresh(),
            'subscription' => $result['subscription'] ?? null,
            'plan_info' => $result['plan_info'] ?? null,
            'order' => $result['order'] ?? null,
        ]);
    }

    /**
     * Flutterwave webhook handler.
     */
    public function webhook(Request $request)
    {
        // Verify signature
        $signature = $request->header('verif-hash');
        if (!FlutterwaveService::validateWebhookSignature($signature ?? '')) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = $request->all();
        $event = $data['event'] ?? '';

        if ($event === 'charge.completed') {
            $txRef = $data['data']['tx_ref'] ?? null;
            if (!$txRef) return response()->json(['status' => 'ignored']);

            $payment = Payment::where('flw_tx_ref', $txRef)->first();
            if (!$payment || $payment->status === 'successful') {
                return response()->json(['status' => 'already_processed']);
            }

            $status = $data['data']['status'] ?? 'failed';

            if ($status === 'successful') {
                $payment->update([
                    'status' => 'successful',
                    'flw_ref' => $data['data']['flw_ref'] ?? null,
                    'payment_method' => $data['data']['payment_type'] ?? null,
                    'flw_response' => $data['data'],
                    'paid_at' => now(),
                ]);

                $this->processSuccessfulPayment($payment);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'flw_response' => $data['data'],
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * List user's payment history.
     */
    public function history(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($payments);
    }

    /**
     * Process a successful payment — activate subscription or confirm order.
     */
    private function processSuccessfulPayment(Payment $payment): array
    {
        $result = [];

        if ($payment->type === 'subscription') {
            $meta = $payment->metadata;
            $shop = Shop::find($payment->shop_id);
            if (!$shop) return $result;

            $plan = $meta['plan'] ?? 'starter';
            $cycle = $meta['billing_cycle'] ?? 'monthly';
            $applyPromo = $meta['apply_promo'] ?? false;

            $expiresAt = $cycle === 'annual' ? now()->addYear() : now()->addMonth();

            // Cancel existing active subscription
            $existingSub = $shop->subscription;
            if ($existingSub && $existingSub->isActive()) {
                $existingSub->update(['status' => 'cancelled']);
            }

            // Create subscription
            $subscription = Subscription::create([
                'shop_id' => $shop->id,
                'plan' => $plan,
                'billing_cycle' => $cycle,
                'amount' => $meta['monthly_amount'] ?? $payment->amount,
                'original_amount' => $meta['original_amount'] ?? null,
                'payment_method' => $payment->payment_method ?? 'flutterwave',
                'payment_reference' => $payment->flw_ref ?? $payment->flw_tx_ref,
                'payment_id' => $payment->id,
                'promo_code' => $applyPromo ? 'LAUNCH63' : null,
                'promo_months_remaining' => $applyPromo ? PlanService::LAUNCH_PROMO['months'] - 1 : null,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            $shop->update(['plan' => $plan]);

            $result['subscription'] = $subscription;
            $result['plan_info'] = PlanService::getPlanInfo($shop->fresh());

            // Qualify referral if this is the first subscription
            $user = $payment->user;
            if ($user && $user->referred_by) {
                $referral = Referral::where('referred_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

                if ($referral) {
                    $referral->update([
                        'status' => 'qualified',
                        'qualified_at' => now(),
                    ]);
                }
            }
        }

        if ($payment->type === 'order') {
            $meta = $payment->metadata;
            $orderId = $meta['order_id'] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'flutterwave',
                        'payment_reference' => $payment->flw_ref ?? $payment->flw_tx_ref,
                        'paid_at' => now(),
                        'status' => $order->status === 'new' ? 'confirmed' : $order->status,
                    ]);
                    $result['order'] = $order->fresh();
                }
            }
        }

        return $result;
    }
}
