<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Product;
use App\Models\DigitalSale;
use App\Models\DownloadToken;
use App\Models\DeliveryZone;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private function generatePaymentCode(int $shopId): string
    {
        do {
            $code = 'SL' . strtoupper(Str::random(6));
        } while (Order::where('payment_code', $code)->exists());
        return $code;
    }

    public function index(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $query = $shop->orders()->with('product:id,name,price,product_type,is_preorder');

        // Filter by preorder
        if ($request->has('preorders_only')) {
            $query->where('is_preorder', true);
        }

        return response()->json(
            $query->latest()->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|email',
            'quantity' => 'nullable|integer|min:1',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'nullable|in:flutterwave,cod',
            'delivery_zone_id' => 'nullable|integer|exists:delivery_zones,id',
            'delivery_address' => 'nullable|string|max:500',
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        $shop = $product->shop;
        $quantity = $request->quantity ?? 1;
        $subtotal = $product->price * $quantity;
        $isPreorder = $product->is_preorder;

        $deliveryZone = null;
        $deliveryFee = 0;
        if ($request->delivery_zone_id) {
            $deliveryZone = DeliveryZone::where('id', $request->delivery_zone_id)
                ->where('shop_id', $shop->id)
                ->where('active', true)
                ->first();
            if ($deliveryZone) {
                $deliveryFee = $deliveryZone->price;
            }
        }

        $total = $subtotal + $deliveryFee;

        // Calculate deposit if preorder (based on product subtotal, not delivery)
        $depositAmount = null;
        if ($isPreorder && $product->preorder_deposit_percent) {
            $depositAmount = (int) ceil($subtotal * $product->preorder_deposit_percent / 100);
        }

        // Commission is on product subtotal only — delivery fee goes 100% to seller
        $commissionRate = PlanService::getCommissionRate($shop);
        $commissionAmount = (int) ceil($subtotal * $commissionRate / 100);
        $sellerAmount = $total - $commissionAmount;

        $paymentMethod = $request->payment_method ?? 'cod';

        $order = Order::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'quantity' => $quantity,
            'total' => $total,
            'note' => $request->note,
            'is_preorder' => $isPreorder,
            'deposit_amount' => $depositAmount,
            'payment_status' => $paymentMethod === 'cod' ? 'unpaid' : 'pending',
            'payment_method' => $paymentMethod,
            'commission_amount' => $commissionAmount,
            'seller_amount' => $sellerAmount,
            'delivery_zone_id' => $deliveryZone?->id,
            'delivery_fee' => $deliveryFee,
            'delivery_address' => $request->delivery_address,
            'payment_code' => $product->isDigital() ? null : $this->generatePaymentCode($shop->id),
        ]);

        $response = ['order' => $order];

        // Handle digital product commission + download token
        if ($product->isDigital()) {
            DigitalSale::create([
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'sale_amount' => $subtotal,
                'commission_percent' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'seller_amount' => $subtotal - $commissionAmount,
            ]);

            $downloadToken = DownloadToken::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'downloads_remaining' => $product->digital_download_limit ?? 5,
                'expires_at' => now()->addDays(30),
            ]);

            $response['download_token'] = $downloadToken->token;
            $response['download_url'] = url("/api/download/{$downloadToken->token}");
        }

        return response()->json($response, 201);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($order->shop_id !== $shop->id, 403);

        $request->validate([
            'status' => 'required|in:new,confirmed,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    /**
     * Manual payment confirmation (Mobile Money, cash, bank transfer).
     * Merchant marks an order as paid after verifying on their phone/bank.
     */
    public function markPaid(Request $request, Order $order)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($order->shop_id !== $shop->id, 403);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Cette commande est déjà marquée comme payée.'], 400);
        }

        $data = $request->validate([
            'source' => 'required|in:momo_mtn,momo_orange,cash,bank,other',
            'reference' => 'nullable|string|max:100',
            'amount' => 'nullable|integer|min:0',
        ]);

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => 'manual',
            'payment_source' => $data['source'],
            'payment_reference' => $data['reference'] ?? null,
            'paid_at' => now(),
            'status' => $order->status === 'new' ? 'confirmed' : $order->status,
        ]);

        return response()->json($order->fresh());
    }

    /**
     * Mark that a reminder (WhatsApp relance) was sent for this order.
     * Timestamp is used to rate-limit the UI and to surface "x min ago" to the merchant.
     */
    public function markReminded(Request $request, Order $order)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($order->shop_id !== $shop->id, 403);

        $order->update([
            'reminder_sent_at' => now(),
            'reminder_count' => ($order->reminder_count ?? 0) + 1,
        ]);

        return response()->json($order->fresh());
    }

    /**
     * Public webhook for SMS-forwarder apps (Android).
     * Accepts raw SMS text, extracts amount + payment_code, auto-matches pending order.
     * Secured by a shop-specific token.
     */
    public function momoSmsWebhook(Request $request, string $token)
    {
        $shop = Shop::where('momo_webhook_token', $token)->first();
        if (!$shop) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $sms = (string) $request->input('text', $request->input('body', ''));
        if ($sms === '') {
            return response()->json(['message' => 'Missing text'], 422);
        }

        // Detect payment code (SL + 6 chars)
        if (!preg_match('/\bSL[A-Z0-9]{6}\b/', strtoupper($sms), $mCode)) {
            return response()->json(['status' => 'no_code']);
        }
        $code = $mCode[0];

        // Detect amount — first integer ≥ 100 in the message
        $amount = null;
        if (preg_match_all('/([0-9][0-9 ,.]{2,})/', $sms, $mAmt)) {
            foreach ($mAmt[1] as $candidate) {
                $n = (int) preg_replace('/[^0-9]/', '', $candidate);
                if ($n >= 100) { $amount = $n; break; }
            }
        }

        // Detect source
        $upper = strtoupper($sms);
        $source = match (true) {
            str_contains($upper, 'MTN') || str_contains($upper, 'MOMO') => 'momo_mtn',
            str_contains($upper, 'ORANGE') || str_contains($upper, 'OM ') => 'momo_orange',
            default => 'other',
        };

        $order = Order::where('shop_id', $shop->id)
            ->where('payment_code', $code)
            ->where('payment_status', '!=', 'paid')
            ->first();

        if (!$order) {
            return response()->json(['status' => 'no_order', 'code' => $code]);
        }

        // Amount sanity check (≥ 90% of expected) — warn but still record
        $matched = $amount === null || $amount >= (int) round($order->total * 0.9);

        $order->update([
            'payment_status' => $matched ? 'paid' : 'unpaid',
            'payment_method' => 'manual',
            'payment_source' => $source,
            'payment_reference' => $amount ? "SMS-{$amount}" : 'SMS',
            'paid_at' => $matched ? now() : null,
            'status' => $matched && $order->status === 'new' ? 'confirmed' : $order->status,
        ]);

        return response()->json([
            'status' => $matched ? 'matched' : 'amount_mismatch',
            'order_id' => $order->id,
            'expected' => $order->total,
            'received' => $amount,
        ]);
    }
}
