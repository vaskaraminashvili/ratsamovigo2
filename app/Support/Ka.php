<?php

namespace App\Support;

/**
 * Georgian wording helpers. Georgian keeps the noun singular after a numeral
 * ("1 ნივთი", "5 ნივთი"), so counts only need the number and the right noun in one place.
 */
class Ka
{
    public static function items(int $count): string
    {
        return self::count($count, 'ნივთი');
    }

    public static function members(int $count): string
    {
        return self::count($count, 'წევრი');
    }

    public static function votes(int $count): string
    {
        return self::count($count, 'ხმა');
    }

    public static function ingredients(int $count): string
    {
        return self::count($count, 'ინგრედიენტი');
    }

    public static function count(int $count, string $noun): string
    {
        return number_format($count, 0, '', ' ').' '.$noun;
    }
}
