<?php

namespace App\Support;

class CatalogLine
{
    /**
     * One-line caption such as "1.39 ₾ · კარფური · 500 მლ". Null when there is no store.
     */
    public static function format(?string $price, ?string $store, ?string $unit): ?string
    {
        $store = trim((string) $store);

        if ($store === '') {
            return null;
        }

        $parts = [];
        $price = trim((string) $price);

        if ($price !== '' && is_numeric($price)) {
            $parts[] = number_format((float) $price, 2, '.', '').' ₾';
        }

        $parts[] = $store;
        $unit = trim((string) $unit);

        if ($unit !== '') {
            $parts[] = $unit;
        }

        return implode(' · ', $parts);
    }
}
