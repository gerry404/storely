<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Services\PlanService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $products = $shop->products()->orderBy('sort_order')->get();

        return response()->json([
            'products' => $products,
            'plan_info' => PlanService::getPlanInfo($shop),
        ]);
    }

    public function store(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        // Plan enforcement: check product limit
        if (!PlanService::canAddProduct($shop)) {
            $limit = PlanService::getProductLimit(PlanService::getEffectivePlan($shop));
            return response()->json([
                'message' => "Vous avez atteint la limite de {$limit} produits pour votre plan. Passez au plan supérieur pour en ajouter plus.",
                'upgrade_required' => true,
            ], 403);
        }

        $imageLimit = PlanService::getImageLimit($shop);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'long_description' => 'nullable|string|max:5000',
            'price' => 'required|integer|min:0',
            'compare_price' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'product_type' => 'nullable|in:physical,digital',
            'is_preorder' => 'nullable|boolean',
            'preorder_available_at' => 'nullable|date|after:today',
            'preorder_deposit_percent' => 'nullable|integer|min:10|max:100',
            'digital_download_limit' => 'nullable|integer|min:1',
            'images' => 'nullable|array|max:' . $imageLimit,
            'images.*' => 'image|max:5120',
        ]);

        // Feature gating
        $productType = $request->product_type ?? 'physical';
        $isPreorder = $request->boolean('is_preorder');

        if ($isPreorder && !PlanService::hasFeature($shop, 'preorders')) {
            return response()->json([
                'message' => 'Les précommandes sont disponibles avec le plan Pro.',
                'upgrade_required' => true,
            ], 403);
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('products', 'public');
            }
        }

        // Handle digital file
        $digitalFilePath = null;
        if ($productType === 'digital' && $request->hasFile('digital_file')) {
            $digitalFilePath = $request->file('digital_file')->store('digital_products', 'local');
        }

        $product = $shop->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'long_description' => $request->long_description,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'stock' => $request->stock ?? 0,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'product_type' => $productType,
            'is_preorder' => $isPreorder,
            'preorder_available_at' => $isPreorder ? $request->preorder_available_at : null,
            'preorder_deposit_percent' => $isPreorder ? $request->preorder_deposit_percent : null,
            'digital_file_path' => $digitalFilePath,
            'digital_download_limit' => $productType === 'digital' ? ($request->digital_download_limit ?? 5) : null,
            'images' => $images ?: null,
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($product->shop_id !== $shop->id, 403);

        $imageLimit = PlanService::getImageLimit($shop);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'long_description' => 'sometimes|nullable|string|max:5000',
            'price' => 'sometimes|integer|min:0',
            'compare_price' => 'sometimes|nullable|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
            'active' => 'sometimes|boolean',
            'category' => 'sometimes|nullable|string|max:100',
            'subcategory' => 'sometimes|nullable|string|max:100',
            'product_type' => 'sometimes|in:physical,digital',
            'is_preorder' => 'sometimes|boolean',
            'preorder_available_at' => 'sometimes|nullable|date',
            'preorder_deposit_percent' => 'sometimes|nullable|integer|min:10|max:100',
            'digital_download_limit' => 'sometimes|nullable|integer|min:1',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'existing_images' => 'nullable|string', // JSON string of kept image paths
        ]);

        $product->update($request->only([
            'name', 'description', 'long_description', 'price', 'compare_price',
            'stock', 'active', 'category', 'subcategory', 'product_type',
            'is_preorder', 'preorder_available_at', 'preorder_deposit_percent',
            'digital_download_limit',
        ]));

        // Handle images: keep existing + add new, respecting plan limit
        $existingImages = $request->existing_images
            ? json_decode($request->existing_images, true) ?? []
            : ($product->images ?? []);

        if ($request->hasFile('images')) {
            $newImages = [];
            foreach ($request->file('images') as $image) {
                $newImages[] = $image->store('products', 'public');
            }
            $allImages = array_merge($existingImages, $newImages);
            $product->update(['images' => array_slice($allImages, 0, $imageLimit)]);
        } else if ($request->has('existing_images')) {
            $product->update(['images' => array_slice($existingImages, 0, $imageLimit)]);
        }

        // Handle digital file update
        if ($request->hasFile('digital_file')) {
            $path = $request->file('digital_file')->store('digital_products', 'local');
            $product->update(['digital_file_path' => $path]);
        }

        return response()->json($product->fresh());
    }

    public function destroy(Request $request, Product $product)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($product->shop_id !== $shop->id, 403);

        $product->delete();

        return response()->json(['message' => 'Produit supprimé']);
    }

    /**
     * Public: get a single product by shop + product slug.
     */
    public function showBySlug(string $shopSlug, string $productSlug)
    {
        $shop = Shop::where('slug', $shopSlug)->where('active', true)->firstOrFail();

        $product = $shop->products()
            ->where('slug', $productSlug)
            ->where('active', true)
            ->firstOrFail();

        // Related products from same category
        $related = $shop->products()
            ->where('active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category, fn($q) => $q->where('category', $product->category))
            ->take(4)
            ->get();

        return response()->json([
            'product' => $product,
            'shop' => $shop->only(['id', 'name', 'slug', 'category', 'logo', 'banner', 'verified', 'plan', 'whatsapp', 'phone']),
            'related' => $related,
        ]);
    }
}
