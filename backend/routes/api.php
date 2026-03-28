<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\DigitalProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Support\Facades\Route;

// ─── Public ────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public storefront
Route::get('/shops/{slug}', [ShopController::class, 'show']);
Route::get('/shops/{slug}/products/{productSlug}', [ProductController::class, 'showBySlug']);
Route::post('/shops/{slug}/reviews', [ReviewController::class, 'store']);
Route::post('/orders', [OrderController::class, 'store']);

// Plans & pricing
Route::get('/plans', [SubscriptionController::class, 'plans']);

// Digital download
Route::get('/download/{token}', [DigitalProductController::class, 'download']);

// Marketplace
Route::get('/marketplace', [MarketplaceController::class, 'explore']);
Route::get('/marketplace/featured', [MarketplaceController::class, 'featured']);
Route::get('/marketplace/cities', [MarketplaceController::class, 'cities']);
Route::get('/marketplace/categories', [MarketplaceController::class, 'categories']);

// Badges (public list)
Route::get('/badges', [BadgeController::class, 'index']);

// Customer chat (public)
Route::post('/shops/{slug}/chat', [ChatController::class, 'customerSend']);
Route::get('/conversations/{conversation}/customer-messages', [ChatController::class, 'customerMessages']);

// Order payment (public — customer pays without auth)
Route::post('/payments/order', [PaymentController::class, 'initOrderPayment']);
Route::post('/payments/verify', [PaymentController::class, 'verifyPayment']);

// Flutterwave webhook (no auth, signature verified in controller)
Route::post('/webhooks/flutterwave', [PaymentController::class, 'webhook']);

// ─── Authenticated ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Shop management
    Route::put('/shop', [ShopController::class, 'update']);
    Route::get('/shop/stats', [ShopController::class, 'stats']);
    Route::post('/shop/logo', [ShopController::class, 'uploadLogo']);
    Route::post('/shop/banner', [ShopController::class, 'uploadBanner']);

    // Promotions
    Route::get('/promotions', [PromotionController::class, 'index']);
    Route::post('/promotions', [PromotionController::class, 'store']);
    Route::put('/promotions/{promotion}', [PromotionController::class, 'update']);
    Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy']);
    Route::post('/promotions/{promotion}/banner', [PromotionController::class, 'uploadBanner']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/digital-file', [DigitalProductController::class, 'upload']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Subscription
    Route::get('/subscription', [SubscriptionController::class, 'current']);
    Route::post('/subscription', [SubscriptionController::class, 'subscribe']);
    Route::delete('/subscription', [SubscriptionController::class, 'cancel']);

    // Reports (Pro)
    Route::get('/reports/revenue', [ReportController::class, 'revenue']);
    Route::get('/reports/customers', [ReportController::class, 'customers']);
    Route::get('/reports/export', [ReportController::class, 'export']);

    // Referrals
    Route::get('/referrals', [ReferralController::class, 'index']);
    Route::post('/referrals/apply', [ReferralController::class, 'applyCode']);

    // Badges
    Route::get('/shop/badges', [BadgeController::class, 'shopBadges']);
    Route::post('/shop/badges/check', [BadgeController::class, 'checkAndAward']);

    // Chat (shop owner)
    Route::get('/conversations', [ChatController::class, 'index']);
    Route::get('/conversations/unread/count', [ChatController::class, 'unreadCount']);
    Route::get('/conversations/{conversation}', [ChatController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);

    // Payments (Flutterwave)
    Route::post('/payments/subscribe', [PaymentController::class, 'initSubscription']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
});

// ─── Admin ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // Users
    Route::get('/users', [AdminController::class, 'users']);
    Route::put('/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

    // Shops
    Route::get('/shops', [AdminController::class, 'shops']);
    Route::put('/shops/{shop}', [AdminController::class, 'updateShop']);
    Route::delete('/shops/{shop}', [AdminController::class, 'deleteShop']);

    // Orders
    Route::get('/orders', [AdminController::class, 'orders']);

    // Payments & Revenue
    Route::get('/payments', [AdminController::class, 'payments']);
    Route::get('/revenue', [AdminController::class, 'revenueReport']);

    // Subscriptions
    Route::get('/subscriptions', [AdminController::class, 'subscriptions']);

    // Badges
    Route::get('/badges', [AdminController::class, 'badges']);
    Route::post('/badges/award', [AdminController::class, 'awardBadge']);
    Route::post('/badges/revoke', [AdminController::class, 'revokeBadge']);

    // Platform settings
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::post('/settings', [AdminController::class, 'updateSetting']);

    // Referrals
    Route::get('/referrals', [AdminController::class, 'referrals']);
    Route::post('/referrals/{referral}/reward', [AdminController::class, 'rewardReferral']);

    // Platform promotions
    Route::get('/promotions', [AdminController::class, 'promotions']);
    Route::post('/promotions', [AdminController::class, 'createPromotion']);
    Route::put('/promotions/{promotion}', [AdminController::class, 'updatePromotion']);
    Route::delete('/promotions/{promotion}', [AdminController::class, 'deletePromotion']);

    // Activity logs
    Route::get('/logs', [AdminController::class, 'logs']);
});
