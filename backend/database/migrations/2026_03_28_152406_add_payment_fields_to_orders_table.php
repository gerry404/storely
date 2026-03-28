<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('status'); // unpaid, pending, paid, failed
            $table->string('payment_method')->nullable()->after('payment_status'); // flutterwave, cod (cash on delivery)
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->integer('commission_amount')->default(0)->after('payment_reference');
            $table->integer('seller_amount')->default(0)->after('commission_amount');
            $table->timestamp('paid_at')->nullable()->after('seller_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'payment_reference', 'commission_amount', 'seller_amount', 'paid_at']);
        });
    }
};
