<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin role on users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'superadmin'])->default('user')->after('email');
        });

        // Flutterwave transaction tracking
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // subscription, boost, order
            $table->string('flw_ref')->nullable()->unique();
            $table->string('flw_tx_ref')->unique();
            $table->integer('amount'); // in XAF
            $table->string('currency', 5)->default('XAF');
            $table->enum('status', ['pending', 'successful', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable(); // mobilemoneyghana, mobilemoneyfranco, card
            $table->json('metadata')->nullable(); // plan, cycle, etc.
            $table->json('flw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('flw_tx_ref');
        });

        // Add payment_id to subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('payment_reference')->constrained('payments')->nullOnDelete();
        });

        // Platform settings (key-value for admin config)
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->timestamps();
        });

        // Admin activity log
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // ban_shop, change_plan, feature_shop, etc.
            $table->string('target_type')->nullable(); // shop, user, order, etc.
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
        });
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('payments');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
