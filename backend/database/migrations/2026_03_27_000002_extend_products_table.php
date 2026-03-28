<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('long_description')->nullable()->after('description');
            $table->string('subcategory')->nullable()->after('category');
            $table->string('product_type')->default('physical')->after('subcategory');
            $table->boolean('is_preorder')->default(false)->after('product_type');
            $table->date('preorder_available_at')->nullable()->after('is_preorder');
            $table->integer('preorder_deposit_percent')->nullable()->after('preorder_available_at');
            $table->string('digital_file_path')->nullable()->after('preorder_deposit_percent');
            $table->integer('digital_download_limit')->nullable()->after('digital_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'long_description', 'subcategory', 'product_type',
                'is_preorder', 'preorder_available_at', 'preorder_deposit_percent',
                'digital_file_path', 'digital_download_limit',
            ]);
        });
    }
};
