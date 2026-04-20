<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->nullable()->after('shop_id')
                ->constrained('delivery_zones')->nullOnDelete();
            $table->unsignedInteger('delivery_fee')->default(0)->after('total');
            $table->string('delivery_address')->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_zone_id']);
            $table->dropColumn(['delivery_zone_id', 'delivery_fee', 'delivery_address']);
        });
    }
};
