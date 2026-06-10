<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class PortfolioVideo extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (PortfolioVideo $video) {
            $parsed = self::parseVideoUrl((string) $video->url);

            if ($parsed === null) {
                throw ValidationException::withMessages([
                    'url' => 'Unesite ispravan YouTube ili Vimeo link.',
                ]);
            }

            $video->provider = $parsed['provider'];
            $video->provider_video_id = $parsed['id'];
        });
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(PortfolioAlbum::class, 'portfolio_album_id');
    }

    public function embedUrl(): string
    {
        return match ($this->provider) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$this->provider_video_id,
            'vimeo' => 'https://player.vimeo.com/video/'.$this->provider_video_id,
            default => '',
        };
    }

    public function thumbnailUrl(): ?string
    {
        return $this->provider === 'youtube'
            ? 'https://img.youtube.com/vi/'.$this->provider_video_id.'/hqdefault.jpg'
            : null;
    }

    /** @return array{provider: string, id: string}|null */
    public static function parseVideoUrl(string $url): ?array
    {
        $url = trim($url);
        $parts = parse_url($url);

        if (! isset($parts['host'])) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', $parts['host']));
        $path = trim($parts['path'] ?? '', '/');

        if (in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['v'] ?? null;

            if (! $id && preg_match('~^(embed|shorts)/([A-Za-z0-9_-]{6,})~', $path, $matches)) {
                $id = $matches[2];
            }

            return self::validYoutubeId($id) ? ['provider' => 'youtube', 'id' => $id] : null;
        }

        if ($host === 'youtu.be') {
            $id = explode('/', $path)[0] ?? null;

            return self::validYoutubeId($id) ? ['provider' => 'youtube', 'id' => $id] : null;
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            if (preg_match('~(?:video/)?(\d{6,})~', $path, $matches)) {
                return ['provider' => 'vimeo', 'id' => $matches[1]];
            }
        }

        return null;
    }

    private static function validYoutubeId(mixed $id): bool
    {
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) === 1;
    }
}
