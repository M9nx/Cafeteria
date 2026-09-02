<?php

declare(strict_types=1);

namespace Cafeteria\Core\Upload;

final class ImageContentValidator
{
    /**
     * @var array<string, list<int>>
     */
    private const MIME_TO_IMAGE_TYPES = [
        'image/jpeg' => [IMAGETYPE_JPEG],
        'image/png' => [IMAGETYPE_PNG],
        'image/webp' => [IMAGETYPE_WEBP],
    ];

    public static function matchesDeclaredMime(string $tmpName, string $mime): bool
    {
        $allowedTypes = self::MIME_TO_IMAGE_TYPES[$mime] ?? [];

        if ($allowedTypes === []) {
            return false;
        }

        $info = @getimagesize($tmpName);

        if ($info === false) {
            return false;
        }

        return in_array($info[2], $allowedTypes, true);
    }
}
