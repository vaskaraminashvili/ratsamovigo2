<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->unsignedInteger('position')->nullable();
        });

        Schema::table('shopping_list_user', function (Blueprint $table) {
            $table->unsignedInteger('position')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('shopping_list_user', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
