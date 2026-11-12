<?php

namespace App\Support;

use App\Enums\Unit;

/**
 * Turns a free-text recipe line ("ხახვი — 500 გრ (წვრილად დაკეპილი)") into list-item fields.
 */
class RecipeIngredient
{
    private const UNITS = [
        'გრ' => Unit::Gram,
        'გ' => Unit::Gram,
        'კგ' => Unit::Kilogram,
        'მლ' => Unit::Milliliter,
        'ლ' => Unit::Liter,
        'ცალი' => Unit::Piece,
        'ც' => Unit::Piece,
        'შეკვრა' => Unit::Pack,
        'კონა' => Unit::Bunch,
    ];

    /**
     * Lines whose unit is not a list unit (spoons, heads, pinches) keep the whole line as the
     * name and get no quantity, so no information is lost.
     *
     * @return array{name: string, quantity: float|null, unit: string|null}
     */
    public static function parse(string $line): array
    {
        $line = trim((string) preg_replace('/\s+/u', ' ', $line));
        $units = implode('|', array_keys(self::UNITS));
        $number = '(\d+(?:[.,]\d+)?)';

        if (preg_match("/^{$number}\s*({$units})(?=\s)\s*(.+)$/u", $line, $m)) {
            return self::row($m[3], $m[1], $m[2]);
        }

        if (preg_match("/^(.+?)\s+[—–-]\s+{$number}\s*({$units})(?=\s|$|[(,.])/u", $line, $m)) {
            return self::row($m[1], $m[2], $m[3]);
        }

        return self::row($line);
    }

    /**
     * Loose name match (either contains the other) so "ნიორი" matches "დაკეპილი ნიორი".
     */
    public static function matches(string $a, string $b): bool
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));

        return mb_strlen($a) >= 3 && mb_strlen($b) >= 3 && (str_contains($a, $b) || str_contains($b, $a));
    }

    /**
     * @return array{name: string, quantity: float|null, unit: string|null}
     */
    private static function row(string $name, ?string $quantity = null, ?string $unit = null): array
    {
        $name = trim((string) preg_replace('/\s*\([^)]*\)/u', '', $name), " \t,.;");

        return [
            'name' => mb_substr($name, 0, 120),
            'quantity' => $quantity === null ? null : (float) str_replace(',', '.', $quantity),
            'unit' => $unit === null ? null : self::UNITS[$unit]->value,
        ];
    }
}
