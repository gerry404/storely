<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\AIProductService;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AIController extends Controller
{
    public function generateProduct(Request $request, AIProductService $service)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,webp,jpg|max:8192',
            'hint' => 'nullable|string|max:200',
        ]);

        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        // Rate-limit per shop: 20 generations / hour
        $key = 'ai-product:' . $shop->id;
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Limite atteinte. Réessayez dans " . ceil($seconds / 60) . " min.",
            ], 429);
        }
        RateLimiter::hit($key, 3600);

        $path = $request->file('image')->getRealPath();

        try {
            $result = $service->generateFromImage($path, $request->input('hint'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }
}
