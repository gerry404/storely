<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('customization')->nullable()->after('active');
            $table->json('featured_products')->nullable()->after('customization');
        });
    }
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['customization', 'featured_products']);
        });
    }
};
