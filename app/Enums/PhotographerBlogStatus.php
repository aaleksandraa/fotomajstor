<?php

namespace App\Enums;

enum PhotographerBlogStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Nacrt',
            self::Pending => 'Na čekanju',
            self::Published => 'Objavljeno',
            self::Rejected => 'Odbijeno',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Pending => 'warning',
            self::Published => 'success',
            self::Rejected => 'danger',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Draft->value => self::Draft->label(),
            self::Pending->value => self::Pending->label(),
            self::Published->value => self::Published->label(),
            self::Rejected->value => self::Rejected->label(),
        ];
    }
}
