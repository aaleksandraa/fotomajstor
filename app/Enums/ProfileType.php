<?php

namespace App\Enums;

enum ProfileType: string
{
    case Individual = 'individual';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Individual => __('Fizičko lice'),
            self::Company => __('Pravno lice / firma'),
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Individual->value => self::Individual->label(),
            self::Company->value => self::Company->label(),
        ];
    }
}
