<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('recipes:import {path? : JSON file, defaults to recipes.json in the project root}')]
#[Description('Import or refresh the recipe catalog from a recipes.json scrape')]
class ImportRecipes extends Command
{
    public function handle(): int
    {
        $path = $this->argument('path') ?? base_path('recipes.json');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);

        if (! is_array($rows)) {
            $this->error('The file is not a JSON list of recipes.');

            return self::FAILURE;
        }

        $count = 0;

        foreach ($rows as $row) {
            if (! isset($row['url'], $row['title']) || ! is_string($row['url']) || ! is_string($row['title'])) {
                continue;
            }

            Recipe::updateOrCreate(['url_hash' => Recipe::hashUrl($row['url'])], [
                'url' => $row['url'],
                'source' => (string) ($row['source'] ?? 'unknown'),
                'title' => $row['title'],
                'categories' => $row['categories'] ?? [],
                'parts' => $row['parts'] ?? [],
                'preparation' => $row['preparation'] ?? [],
            ]);
            $count++;
        }

        $this->info("Imported {$count} recipes.");

        return self::SUCCESS;
    }
}
