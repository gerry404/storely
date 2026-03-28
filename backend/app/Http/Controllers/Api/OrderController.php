<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Product;
use App\Models\DigitalSale;
use App\Models\DownloadToken;
use App\Services\PlanService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
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
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        $shop = $product->shop;
        $quantity = $request->quantity ?? 1;
        $total = $product->price * $quantity;
        $isPreorder = $product->is_preorder;

        // Calculate deposit if preorder
        $depositAmount = null;
        if ($isPreorder && $product->preorder_deposit_percent) {
            $depositAmount = (int) ceil($total * $product->preorder_deposit_percent / 100);
        }

        // Calculate commission for physical products (same tiers as digital)
        $commissionRate = PlanService::getCommissionRate($shop);
        $commissionAmount = (int) ceil($total * $commissionRate / 100);
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
        ]);

        $response = ['order' => $order];

        // Handle digital product commission + download token
        if ($product->isDigital()) {
            DigitalSale::create([
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'order_id' => $order->id,
                'sale_amount' => $total,
                'commission_percent' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'seller_amount' => $sellerAmount,
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
}
