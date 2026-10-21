<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->unsignedBigInteger('catalog_product_id')->nullable();
            $table->string('catalog_store_name', 80)->nullable();
            $table->string('catalog_store_slug', 40)->nullable();
            $table->decimal('catalog_price', 8, 2)->nullable();
            $table->string('catalog_unit', 40)->nullable();
            $table->string('catalog_url', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropColumn([
                'catalog_product_id',
                'catalog_store_name',
                'catalog_store_slug',
                'catalog_price',
                'catalog_unit',
                'catalog_url',
            ]);
        });
    }
};
