<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('momo_webhook_token', 48)->nullable()->unique()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['momo_webhook_token']);
            $table->dropColumn('momo_webhook_token');
        });
    }
};
