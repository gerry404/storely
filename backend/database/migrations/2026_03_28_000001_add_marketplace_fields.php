<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->timestamp('featured_at')->nullable()->after('active');
            $table->timestamp('boost_expires_at')->nullable()->after('featured_at');
            $table->string('country', 3)->default('CM')->after('city');
            $table->string('currency', 5)->default('XAF')->after('country');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 12)->nullable()->unique()->after('password');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
            $table->string('locale', 5)->default('fr')->after('referred_by');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['featured_at', 'boost_expires_at', 'country', 'currency']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['referral_code', 'referred_by', 'locale']);
        });
    }
};
