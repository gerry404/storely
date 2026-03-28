<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('plan'); // starter, pro
            $table->integer('amount');
            $table->string('payment_method')->nullable(); // orange_money, mtn_momo, card
            $table->string('payment_reference')->nullable();
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
