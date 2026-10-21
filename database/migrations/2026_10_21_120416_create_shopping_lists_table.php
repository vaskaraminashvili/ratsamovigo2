<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('bought_behavior')->default('strike');
            $table->boolean('group_by_priority')->default(true);
            $table->string('invite_token', 64)->unique();
            $table->timestamps();
        });

        Schema::create('shopping_list_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shopping_list_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_list_user');
        Schema::dropIfExists('shopping_lists');
    }
};
