<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('claimed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->decimal('quantity', 8, 2)->nullable();
            $table->string('unit')->default('ცალი');
            $table->string('priority')->default('normal');
            $table->foreignId('bought_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('bought_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_items');
    }
};
