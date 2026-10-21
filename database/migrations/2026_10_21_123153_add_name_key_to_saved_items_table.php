<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_items', function (Blueprint $table) {
            $table->string('name_key')->nullable()->after('name');
        });

        $seen = [];

        foreach (DB::table('saved_items')->orderBy('id')->get(['id', 'user_id', 'name']) as $row) {
            $key = mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $row->name)));
            $unique = $row->user_id.'|'.$key;

            if (isset($seen[$unique])) {
                DB::table('saved_items')->where('id', $row->id)->delete();

                continue;
            }

            $seen[$unique] = true;
            DB::table('saved_items')->where('id', $row->id)->update(['name_key' => $key]);
        }

        Schema::table('saved_items', function (Blueprint $table) {
            $table->string('name_key')->nullable(false)->change();
            $table->unique(['user_id', 'name_key']);
        });
    }

    public function down(): void
    {
        Schema::table('saved_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'name_key']);
            $table->dropColumn('name_key');
        });
    }
};
