<?php

declare(strict_types=1);

namespace Cafeteria\Support;

final class PublicFileUrl
{
    private const FALLBACK = '/assets/images/products/placeholder.svg';

    public static function fromStoredPath(?string $storedPath): string
    {
        if ($storedPath === null) {
            return self::FALLBACK;
        }

        $path = str_replace('\\', '/', trim($storedPath));

        if ($path === '') {
            return self::FALLBACK;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        ) {
            return $path;
        }

        if (
            preg_match(
                '#^storage/uploads/(products|profiles)/([a-f0-9]{32}\.(?:jpg|jpeg|png|webp))$#i',
                $path,
                $matches,
            ) === 1
        ) {
            return '/media/' . $matches[1] . '/' . $matches[2];
        }

        return self::FALLBACK;
    }
}
