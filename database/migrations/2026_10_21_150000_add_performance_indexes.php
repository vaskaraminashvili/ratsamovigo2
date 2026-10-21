<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->index(['shopping_list_id', 'bought_at']);
            $table->index(['shopping_list_id', 'group_name']);
        });

        Schema::table('shopping_list_user', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropIndex(['shopping_list_id', 'bought_at']);
            $table->dropIndex(['shopping_list_id', 'group_name']);
        });

        Schema::table('shopping_list_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
