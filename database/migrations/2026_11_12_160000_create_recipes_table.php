<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('source', 60);
            $table->text('url');
            $table->char('url_hash', 40)->unique();
            $table->string('title')->index();
            $table->json('categories');
            $table->json('parts');
            $table->json('preparation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
