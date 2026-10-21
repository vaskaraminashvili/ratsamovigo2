<?php

namespace App\Enums;

enum Priority: string
{
    case Urgent = 'urgent';
    case Normal = 'normal';
    case Maybe = 'maybe';

    public function label(): string
    {
        return match ($this) {
            self::Urgent => 'სასწრაფო',
            self::Normal => 'საყიდელი',
            self::Maybe => 'თუ ნახავ',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Urgent => 'დღეს საღამოს სჭირდებათ',
            self::Normal => '',
            self::Maybe => 'არ არის აუცილებელი',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Urgent => 0,
            self::Normal => 1,
            self::Maybe => 2,
        };
    }
}
