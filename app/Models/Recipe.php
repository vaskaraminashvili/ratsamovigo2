<?php

namespace App\Models;

use App\Support\RecipeIngredient;
use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $source
 * @property string $url
 * @property string $title
 * @property list<string> $categories
 * @property list<array{name: string|null, ingredients: list<string>}> $parts
 * @property list<string> $preparation
 */
#[Fillable(['source', 'url', 'url_hash', 'title', 'categories', 'parts', 'preparation'])]
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    /**
     * Source URLs can be too long to index, so uniqueness lives on a hash of the URL.
     */
    public static function hashUrl(string $url): string
    {
        return sha1($url);
    }

    protected static function booted(): void
    {
        static::saving(fn (Recipe $recipe) => $recipe->url_hash = self::hashUrl($recipe->url));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'categories' => 'json:unicode',
            'parts' => 'json:unicode',
            'preparation' => 'json:unicode',
        ];
    }

    /**
     * Every ingredient of the recipe, flattened across its parts and split into name, quantity and unit.
     *
     * @return list<array{label: string, name: string, quantity: float|null, unit: string|null}>
     */
    public function ingredientLines(): array
    {
        $lines = [];

        foreach ($this->parts as $part) {
            foreach ($part['ingredients'] ?? [] as $line) {
                if (is_string($line) && trim($line) !== '') {
                    $lines[] = ['label' => trim($line), ...RecipeIngredient::parse($line)];
                }
            }
        }

        return $lines;
    }
}
