<?php

namespace App\Enums;

enum ServiceType: string
{
    case Photographer = 'photographer';
    case Videographer = 'videographer';
    case PhotographerVideographer = 'photographer_videographer';

    public function label(): string
    {
        return match ($this) {
            self::Photographer => __('Fotograf'),
            self::Videographer => __('Videograf'),
            self::PhotographerVideographer => __('Fotograf & Videograf'),
        };
    }

    public function seoSlug(): string
    {
        return match ($this) {
            self::Photographer => 'fotograf',
            self::Videographer => 'videograf',
            self::PhotographerVideographer => 'fotograf-videograf',
        };
    }

    public function searchTitle(): string
    {
        return match ($this) {
            self::Photographer => __('Fotograf'),
            self::Videographer => __('Videograf'),
            self::PhotographerVideographer => __('Fotograf i videograf'),
        };
    }

    /** @return array<int, string> */
    public function matchingValues(): array
    {
        return match ($this) {
            self::Photographer => [self::Photographer->value, self::PhotographerVideographer->value],
            self::Videographer => [self::Videographer->value, self::PhotographerVideographer->value],
            self::PhotographerVideographer => [self::PhotographerVideographer->value],
        };
    }

    public static function fromSeoSlug(string $slug): ?self
    {
        return match ($slug) {
            'fotograf' => self::Photographer,
            'videograf' => self::Videographer,
            'fotograf-videograf' => self::PhotographerVideographer,
            default => null,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Photographer->value => self::Photographer->label(),
            self::Videographer->value => self::Videographer->label(),
            self::PhotographerVideographer->value => self::PhotographerVideographer->label(),
        ];
    }
}
