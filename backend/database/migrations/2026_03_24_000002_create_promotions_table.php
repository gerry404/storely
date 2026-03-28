<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('discount'); // discount, banner, flash_sale, free_shipping
            $table->integer('discount_percent')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('badge_text')->nullable(); // e.g. "PROMO", "-20%", "NOUVEAU"
            $table->string('badge_color')->nullable();
            $table->json('product_ids')->nullable(); // products this promo applies to
            $table->boolean('active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
