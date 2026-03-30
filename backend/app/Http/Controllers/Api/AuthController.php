<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\PlatformPromotion;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
            'shop_name' => 'required|string|max:255',
            'referral_code' => 'nullable|string|size:8',
            'country' => 'nullable|string|size:2',
            'phone_code' => 'nullable|string|max:6',
        ]);

        // Resolve referrer
        $referrerId = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referred_by' => $referrerId,
        ]);

        // Create referral record
        if ($referrerId) {
            \App\Models\Referral::create([
                'referrer_id' => $referrerId,
                'referred_id' => $user->id,
                'status' => 'pending',
                'reward_months' => 1,
            ]);
        }

        $slug = Str::slug($request->shop_name);
        $originalSlug = $slug;
        $counter = 1;
        while (Shop::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Check for auto-register promotion
        $promo = PlatformPromotion::findAutoRegisterPromo();
        $promoPlan = $promo ? $promo->plan : 'free';

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => $request->shop_name,
            'slug' => $slug,
            'phone' => $request->phone,
            'whatsapp' => $request->phone,
            'plan' => $promoPlan,
            'country' => $request->country ?? 'CM',
        ]);

        // Apply promo: create subscription + increment counter
        $appliedPromo = null;
        if ($promo) {
            Subscription::create([
                'shop_id' => $shop->id,
                'plan' => $promo->plan,
                'billing_cycle' => 'monthly',
                'amount' => 0,
                'payment_method' => 'promo',
                'payment_reference' => 'PROMO-' . $promo->id,
                'promo_code' => $promo->promo_code ?? ('AUTO-' . $promo->id),
                'original_amount' => 0,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays($promo->duration_days),
            ]);
            $promo->increment('used_count');
            $shop->refresh();
            $appliedPromo = [
                'name' => $promo->name,
                'plan' => $promo->plan,
                'duration_days' => $promo->duration_days,
                'remaining' => $promo->remaining(),
            ];
        }

        $token = $user->createToken('storely')->plainTextToken;

        return response()->json([
            'user' => $user,
            'shop' => $shop,
            'plan_info' => PlanService::getPlanInfo($shop),
            'token' => $token,
            'promo_applied' => $appliedPromo,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $token = $user->createToken('storely')->plainTextToken;
        $shop = Shop::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'shop' => $shop,
            'plan_info' => $shop ? PlanService::getPlanInfo($shop) : null,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->with('subscription')->first();

        return response()->json([
            'user' => $user,
            'shop' => $shop,
            'plan_info' => $shop ? PlanService::getPlanInfo($shop) : null,
            'is_admin' => in_array($user->role, ['admin', 'superadmin']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté']);
    }

    public function googleRedirect()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function googleCallback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentification Google échouée.'], 401);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Existing user — login
            $token = $user->createToken('storely')->plainTextToken;
            $shop = Shop::where('user_id', $user->id)->first();

            return response()->json([
                'user' => $user,
                'shop' => $shop,
                'plan_info' => $shop ? PlanService::getPlanInfo($shop) : null,
                'token' => $token,
                'is_new' => false,
            ]);
        }

        // New user — register
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'password' => Hash::make(Str::random(24)),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        $shopName = explode(' ', $googleUser->getName())[0] . "'s Shop";
        $slug = Str::slug($shopName);
        $originalSlug = $slug;
        $counter = 1;
        while (Shop::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $promo = PlatformPromotion::findAutoRegisterPromo();
        $promoPlan = $promo ? $promo->plan : 'free';

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => $shopName,
            'slug' => $slug,
            'plan' => $promoPlan,
            'country' => 'CM',
        ]);

        if ($promo) {
            Subscription::create([
                'shop_id' => $shop->id,
                'plan' => $promo->plan,
                'billing_cycle' => 'monthly',
                'amount' => 0,
                'payment_method' => 'promo',
                'payment_reference' => 'PROMO-' . $promo->id,
                'promo_code' => $promo->promo_code ?? ('AUTO-' . $promo->id),
                'original_amount' => 0,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays($promo->duration_days),
            ]);
            $promo->increment('used_count');
            $shop->refresh();
        }

        $token = $user->createToken('storely')->plainTextToken;

        return response()->json([
            'user' => $user,
            'shop' => $shop,
            'plan_info' => PlanService::getPlanInfo($shop),
            'token' => $token,
            'is_new' => true,
        ], 201);
    }
}
