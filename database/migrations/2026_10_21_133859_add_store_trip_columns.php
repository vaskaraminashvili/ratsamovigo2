<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->foreignId('shopping_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shopping_started_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('going_message', 160)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shopping_by');
            $table->dropColumn('shopping_started_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('going_message');
        });
    }
};
