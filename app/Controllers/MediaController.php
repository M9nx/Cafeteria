<?php

declare(strict_types=1);

namespace Cafeteria\Controllers;

use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;

final class MediaController
{
    /** @var list<string> */
    private const KINDS = ['products', 'profiles'];

    public function __construct(
        private readonly string $uploadsRoot,
    ) {
    }

    public function show(
        Request $request,
        string $kind,
        string $filename,
    ): Response {
        if (!in_array($kind, self::KINDS, true)) {
            return Response::html('Not Found', 404);
        }

        if (
            preg_match(
                '/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i',
                $filename,
            ) !== 1
        ) {
            return Response::html('Not Found', 404);
        }

        $kindDirectory = realpath(
            rtrim($this->uploadsRoot, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $kind
        );

        $absolute = realpath(
            rtrim($this->uploadsRoot, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $kind
            . DIRECTORY_SEPARATOR
            . $filename
        );

        if (
            $kindDirectory === false
            || $absolute === false
            || !str_starts_with($absolute, $kindDirectory . DIRECTORY_SEPARATOR)
        ) {
            return Response::html('Not Found', 404);
        }

        $contents = file_get_contents($absolute);

        if ($contents === false) {
            return Response::html('Not Found', 404);
        }

        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return new Response(
            $contents,
            200,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
