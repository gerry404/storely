<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'slug' => 'verified',
                'name' => 'Boutique vérifiée',
                'name_en' => 'Verified Shop',
                'description' => 'Boutique vérifiée par l\'équipe Storely',
                'icon' => 'verified',
                'color' => '#3B82F6',
                'criteria_type' => 'manual',
                'criteria_value' => 0,
                'sort_order' => 1,
            ],
            [
                'slug' => 'top-seller',
                'name' => 'Top vendeur',
                'name_en' => 'Top Seller',
                'description' => 'Plus de 100 ventes réalisées',
                'icon' => 'star',
                'color' => '#F59E0B',
                'criteria_type' => 'orders_count',
                'criteria_value' => 100,
                'sort_order' => 2,
            ],
            [
                'slug' => 'fast-responder',
                'name' => 'Réponse rapide',
                'name_en' => 'Fast Responder',
                'description' => 'Répond en moins de 2h en moyenne',
                'icon' => 'lightning',
                'color' => '#10B981',
                'criteria_type' => 'response_time',
                'criteria_value' => 120,
                'sort_order' => 3,
            ],
            [
                'slug' => 'highly-rated',
                'name' => 'Très bien noté',
                'name_en' => 'Highly Rated',
                'description' => 'Note moyenne supérieure à 4.5/5',
                'icon' => 'heart',
                'color' => '#EF4444',
                'criteria_type' => 'reviews_avg',
                'criteria_value' => 45, // 4.5 * 10
                'sort_order' => 4,
            ],
            [
                'slug' => 'rising-star',
                'name' => 'Étoile montante',
                'name_en' => 'Rising Star',
                'description' => 'Plus de 50 ventes en 30 jours',
                'icon' => 'rocket',
                'color' => '#8B5CF6',
                'criteria_type' => 'monthly_orders',
                'criteria_value' => 50,
                'sort_order' => 5,
            ],
            [
                'slug' => 'trusted',
                'name' => 'Vendeur de confiance',
                'name_en' => 'Trusted Seller',
                'description' => 'Plus de 6 mois d\'activité sans litige',
                'icon' => 'shield',
                'color' => '#06B6D4',
                'criteria_type' => 'account_age_months',
                'criteria_value' => 6,
                'sort_order' => 6,
            ],
            [
                'slug' => 'digital-pro',
                'name' => 'Expert digital',
                'name_en' => 'Digital Pro',
                'description' => 'Plus de 50 ventes de produits digitaux',
                'icon' => 'download',
                'color' => '#EC4899',
                'criteria_type' => 'digital_sales_count',
                'criteria_value' => 50,
                'sort_order' => 7,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
