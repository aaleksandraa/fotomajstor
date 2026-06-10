<?php

namespace App\Enums;

enum BlogStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nacrt',
            self::Published => 'Objavljeno',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Draft->value => self::Draft->label(),
            self::Published->value => self::Published->label(),
        ];
    }
}
