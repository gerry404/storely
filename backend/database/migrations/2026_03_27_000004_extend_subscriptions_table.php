<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('billing_cycle')->default('monthly')->after('plan');
            $table->string('promo_code')->nullable()->after('payment_reference');
            $table->integer('original_amount')->nullable()->after('promo_code');
            $table->integer('promo_months_remaining')->nullable()->after('original_amount');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'promo_code', 'original_amount', 'promo_months_remaining']);
        });
    }
};
