<?php

namespace App\Enums;

enum Unit: string
{
    case Piece = 'ცალი';
    case Kilogram = 'კგ';
    case Gram = 'გ';
    case Liter = 'ლ';
    case Milliliter = 'მლ';
    case Pack = 'შეკვრა';
    case Bunch = 'კონა';

    public function fullName(): string
    {
        return match ($this) {
            self::Piece => 'ცალი',
            self::Kilogram => 'კილოგრამი',
            self::Gram => 'გრამი',
            self::Liter => 'ლიტრი',
            self::Milliliter => 'მილილიტრი',
            self::Pack => 'შეკვრა',
            self::Bunch => 'კონა',
        };
    }

    public function step(): float
    {
        return match ($this) {
            self::Kilogram, self::Liter => 0.5,
            self::Gram, self::Milliliter => 100,
            default => 1,
        };
    }

    public function start(): float
    {
        return match ($this) {
            self::Gram, self::Milliliter => 250,
            default => 1,
        };
    }

    /**
     * @return list<array{key: string, full: string, step: float, start: float}>
     */
    public static function options(): array
    {
        return array_map(fn (self $unit) => [
            'key' => $unit->value,
            'full' => $unit->fullName(),
            'step' => $unit->step(),
            'start' => $unit->start(),
        ], self::cases());
    }
}
