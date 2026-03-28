<?php

namespace App\Services;

use App\Models\Shop;

class PlanService
{
    /**
     * Plan limits configuration.
     */
    public const LIMITS = [
        'free' => [
            'products' => 5,
            'images_per_product' => 1,
            'commission_percent' => 15,
        ],
        'starter' => [
            'products' => 20,
            'images_per_product' => 3,
            'commission_percent' => 8,
        ],
        'pro' => [
            'products' => PHP_INT_MAX,
            'images_per_product' => 10,
            'commission_percent' => 4,
        ],
    ];

    /**
     * Pricing in F CFA.
     */
    public const PRICING = [
        'starter' => [
            'monthly' => 3900,
            'annual_monthly' => 2900,
            'annual_total' => 34800,
        ],
        'pro' => [
            'monthly' => 9900,
            'annual_monthly' => 7400,
            'annual_total' => 88800,
        ],
    ];

    /**
     * Launch promo: 63% off for first 3 months.
     */
    public const LAUNCH_PROMO = [
        'active' => true,
        'discount_percent' => 63,
        'months' => 3,
        'prices' => [
            'starter' => 1443,
            'pro' => 3663,
        ],
    ];

    /**
     * Features gated by plan.
     */
    public const FEATURES = [
        'analytics_basic'    => ['starter', 'pro'],
        'analytics_full'     => ['pro'],
        'order_management'   => ['starter', 'pro'],
        'stock_management'   => ['pro'],
        'promotions'         => ['pro'],
        'preorders'          => ['pro'],
        'verified_badge'     => ['pro'],
        'branding_removed'   => ['pro'],
        'branding_reduced'   => ['starter', 'pro'],
        'digital_products'   => ['free', 'starter', 'pro'],
        'export'             => ['pro'],
        'advanced_management' => ['pro'],
        'whatsapp_button'    => ['starter', 'pro'],
    ];

    /**
     * Get the effective plan for a shop (considering active subscription).
     */
    public static function getEffectivePlan(Shop $shop): string
    {
        return $shop->getEffectivePlan();
    }

    /**
     * Check if the shop can add another product.
     */
    public static function canAddProduct(Shop $shop): bool
    {
        $plan = self::getEffectivePlan($shop);
        $limit = self::LIMITS[$plan]['products'] ?? 5;
        return $shop->products()->count() < $limit;
    }

    /**
     * Get max images per product for the shop's plan.
     */
    public static function getImageLimit(Shop $shop): int
    {
        $plan = self::getEffectivePlan($shop);
        return self::LIMITS[$plan]['images_per_product'] ?? 1;
    }

    /**
     * Get the commission rate for digital sales.
     */
    public static function getCommissionRate(Shop $shop): int
    {
        $plan = self::getEffectivePlan($shop);
        return self::LIMITS[$plan]['commission_percent'] ?? 15;
    }

    /**
     * Check if the shop has access to a specific feature.
     */
    public static function hasFeature(Shop $shop, string $feature): bool
    {
        $plan = self::getEffectivePlan($shop);
        $allowedPlans = self::FEATURES[$feature] ?? [];
        return in_array($plan, $allowedPlans);
    }

    /**
     * Get the product limit for a plan.
     */
    public static function getProductLimit(string $plan): int
    {
        return self::LIMITS[$plan]['products'] ?? 5;
    }

    /**
     * Get full plan info for API response.
     */
    public static function getPlanInfo(Shop $shop): array
    {
        $plan = self::getEffectivePlan($shop);
        $limits = self::LIMITS[$plan];

        return [
            'current_plan' => $plan,
            'product_limit' => $limits['products'] === PHP_INT_MAX ? -1 : $limits['products'],
            'product_count' => $shop->products()->count(),
            'image_limit' => $limits['images_per_product'],
            'commission_rate' => $limits['commission_percent'],
            'features' => collect(self::FEATURES)->mapWithKeys(function ($plans, $feature) use ($plan) {
                return [$feature => in_array($plan, $plans)];
            })->all(),
        ];
    }

    /**
     * Calculate price for a subscription.
     */
    public static function calculatePrice(string $plan, string $cycle, bool $applyPromo = false): array
    {
        $pricing = self::PRICING[$plan] ?? null;
        if (!$pricing) return ['amount' => 0];

        if ($cycle === 'annual') {
            return [
                'amount' => $pricing['annual_monthly'],
                'total' => $pricing['annual_total'],
                'original_monthly' => $pricing['monthly'],
                'savings_per_year' => ($pricing['monthly'] * 12) - $pricing['annual_total'],
            ];
        }

        $amount = $pricing['monthly'];
        $result = ['amount' => $amount, 'original_amount' => null];

        if ($applyPromo && self::LAUNCH_PROMO['active']) {
            $result['original_amount'] = $amount;
            $result['amount'] = self::LAUNCH_PROMO['prices'][$plan] ?? $amount;
            $result['promo_months'] = self::LAUNCH_PROMO['months'];
            $result['discount_percent'] = self::LAUNCH_PROMO['discount_percent'];
        }

        return $result;
    }

    /**
     * Get all plans info for pricing page.
     */
    public static function getAllPlans(): array
    {
        return [
            'plans' => [
                [
                    'id' => 'free',
                    'name' => 'Gratuit',
                    'price_monthly' => 0,
                    'price_annual' => 0,
                    'limits' => self::LIMITS['free'],
                    'features' => [
                        'Jusqu\'à 5 produits',
                        '1 image par produit',
                        'Lien boutique storely.app',
                        'Contact WhatsApp',
                        'Produits digitaux (commission 15%)',
                    ],
                    'popular' => false,
                ],
                [
                    'id' => 'starter',
                    'name' => 'Starter',
                    'price_monthly' => self::PRICING['starter']['monthly'],
                    'price_annual_monthly' => self::PRICING['starter']['annual_monthly'],
                    'price_annual_total' => self::PRICING['starter']['annual_total'],
                    'promo_price' => self::LAUNCH_PROMO['prices']['starter'],
                    'limits' => self::LIMITS['starter'],
                    'features' => [
                        'Jusqu\'à 20 produits',
                        '3 images par produit',
                        'Statistiques de visites',
                        'Gestion des commandes',
                        'Bouton WhatsApp',
                        'Branding Storely réduit',
                        'Produits digitaux (commission 8%)',
                    ],
                    'popular' => false,
                ],
                [
                    'id' => 'pro',
                    'name' => 'Pro',
                    'price_monthly' => self::PRICING['pro']['monthly'],
                    'price_annual_monthly' => self::PRICING['pro']['annual_monthly'],
                    'price_annual_total' => self::PRICING['pro']['annual_total'],
                    'promo_price' => self::LAUNCH_PROMO['prices']['pro'],
                    'limits' => self::LIMITS['pro'],
                    'features' => [
                        'Produits illimités',
                        '10 images par produit',
                        'Statistiques complètes',
                        'Gestion des stocks',
                        'Commandes en ligne',
                        'Codes promo & badges',
                        'Badge boutique vérifiée',
                        'Précommandes',
                        'Export de données',
                        'Aucun branding Storely',
                        'Produits digitaux (commission 4%)',
                        'Support prioritaire',
                    ],
                    'popular' => true,
                ],
            ],
            'promo' => self::LAUNCH_PROMO,
            'billing_note' => 'Paiement par Mobile Money (Orange, MTN) · Annulez à tout moment',
        ];
    }
}
