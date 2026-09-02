<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Upload;

use Cafeteria\Core\Upload\ImageContentValidator;
use PHPUnit\Framework\TestCase;

final class ImageContentValidatorTest extends TestCase
{
    public function test_rejects_non_image_bytes_with_jpeg_mime(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cafeteria-upload-');

        self::assertNotFalse($path);

        file_put_contents($path, 'not-an-image');

        self::assertFalse(
            ImageContentValidator::matchesDeclaredMime($path, 'image/jpeg'),
        );

        unlink($path);
    }

    public function test_accepts_valid_png_bytes(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cafeteria-upload-');

        self::assertNotFalse($path);

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+X2ZkAAAAASUVORK5CYII=',
            true,
        );

        self::assertNotFalse($png);
        file_put_contents($path, $png);

        self::assertTrue(
            ImageContentValidator::matchesDeclaredMime($path, 'image/png'),
        );

        unlink($path);
    }
}
