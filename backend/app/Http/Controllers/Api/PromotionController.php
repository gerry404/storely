<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Shop;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        return response()->json($shop->promotions()->latest()->get());
    }

    public function store(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:discount,banner,flash_sale,free_shipping',
            'discount_percent' => 'nullable|integer|min:1|max:99',
            'discount_amount' => 'nullable|integer|min:0',
            'badge_text' => 'nullable|string|max:30',
            'badge_color' => 'nullable|string|max:20',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'active' => 'sometimes|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $promo = $shop->promotions()->create($request->only([
            'title', 'description', 'type', 'discount_percent', 'discount_amount',
            'badge_text', 'badge_color', 'product_ids', 'active', 'starts_at', 'ends_at',
        ]));

        return response()->json($promo, 201);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($promotion->shop_id !== $shop->id, 403);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'sometimes|in:discount,banner,flash_sale,free_shipping',
            'discount_percent' => 'nullable|integer|min:1|max:99',
            'discount_amount' => 'nullable|integer|min:0',
            'badge_text' => 'nullable|string|max:30',
            'badge_color' => 'nullable|string|max:20',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'active' => 'sometimes|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $promotion->update($request->only([
            'title', 'description', 'type', 'discount_percent', 'discount_amount',
            'badge_text', 'badge_color', 'product_ids', 'active', 'starts_at', 'ends_at',
        ]));

        return response()->json($promotion);
    }

    public function destroy(Request $request, Promotion $promotion)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($promotion->shop_id !== $shop->id, 403);
        $promotion->delete();
        return response()->json(['message' => 'Promotion supprimée']);
    }

    public function uploadBanner(Request $request, Promotion $promotion)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($promotion->shop_id !== $shop->id, 403);

        $request->validate(['banner' => 'required|image|max:5120']);
        $path = $request->file('banner')->store('promotions', 'public');
        $promotion->update(['banner_image' => $path]);

        return response()->json(['banner_image' => $path]);
    }
}
