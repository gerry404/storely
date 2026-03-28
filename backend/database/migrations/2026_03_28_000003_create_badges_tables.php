<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('description');
            $table->string('icon')->default('star');
            $table->string('color')->default('#FF6B2C');
            $table->string('criteria_type'); // orders_count, reviews_avg, response_time, sales_total, verified
            $table->integer('criteria_value')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shop_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['shop_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_badges');
        Schema::dropIfExists('badges');
    }
};
