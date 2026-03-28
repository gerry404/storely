<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->json('images')->nullable()->after('comment');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()->after('shop_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['images', 'product_id']);
        });
    }
};
