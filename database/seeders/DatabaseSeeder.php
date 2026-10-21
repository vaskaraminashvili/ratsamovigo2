<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Model events must stay on while seeding: lists generate invite tokens and saved items
 * compute their match key in model hooks.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->isProduction()) {
            $this->call(DemoFamilySeeder::class);
        }
    }
}
