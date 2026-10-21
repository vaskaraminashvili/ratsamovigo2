<?php

namespace App\Support;

/**
 * Turns Latin-letter Georgian ("nabeghlavi") into Georgian script ("ნაბეღლავი"),
 * so people can search the catalog without switching keyboard layout.
 */
class GeorgianLatin
{
    /**
     * Two-letter combinations. They are matched before single letters.
     *
     * @var array<string, string>
     */
    private const PAIRS = [
        'sh' => 'შ',
        'ch' => 'ჩ',
        'zh' => 'ჟ',
        'gh' => 'ღ',
        'kh' => 'ხ',
        'dz' => 'ძ',
        'ts' => 'ც',
        'tc' => 'ჭ',
        'th' => 'თ',
    ];

    /**
     * @var array<string, string>
     */
    private const LETTERS = [
        'a' => 'ა',
        'b' => 'ბ',
        'c' => 'ც',
        'd' => 'დ',
        'e' => 'ე',
        'f' => 'ფ',
        'g' => 'გ',
        'h' => 'ჰ',
        'i' => 'ი',
        'j' => 'ჯ',
        'k' => 'კ',
        'l' => 'ლ',
        'm' => 'მ',
        'n' => 'ნ',
        'o' => 'ო',
        'p' => 'პ',
        'q' => 'ქ',
        'r' => 'რ',
        's' => 'ს',
        't' => 'ტ',
        'u' => 'უ',
        'v' => 'ვ',
        'w' => 'წ',
        'x' => 'ხ',
        'y' => 'ყ',
        'z' => 'ზ',
    ];

    public function toGeorgian(string $text): string
    {
        if (preg_match('/[a-z]/i', $text) !== 1) {
            return $text;
        }

        return strtr(strtr(mb_strtolower($text), self::PAIRS), self::LETTERS);
    }
}
