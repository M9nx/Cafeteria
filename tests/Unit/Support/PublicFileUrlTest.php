<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Cafeteria\Support\PublicFileUrl;
use PHPUnit\Framework\TestCase;

final class PublicFileUrlTest extends TestCase
{
    public function test_maps_storage_upload_path_to_media_url(): void
    {
        $url = PublicFileUrl::fromStoredPath(
            'storage/uploads/products/b6495a41d591516a95f407ff03b65f52.jpg',
        );

        self::assertSame(
            '/media/products/b6495a41d591516a95f407ff03b65f52.jpg',
            $url,
        );
    }

    public function test_keeps_public_asset_paths(): void
    {
        self::assertSame(
            '/assets/images/products/tea.svg',
            PublicFileUrl::fromStoredPath('/assets/images/products/tea.svg'),
        );
    }

    public function test_falls_back_for_empty_or_unsafe_paths(): void
    {
        $fallback = '/assets/images/products/placeholder.svg';

        self::assertSame($fallback, PublicFileUrl::fromStoredPath(null));
        self::assertSame($fallback, PublicFileUrl::fromStoredPath(''));
        self::assertSame(
            $fallback,
            PublicFileUrl::fromStoredPath('storage/uploads/products/../.env'),
        );
    }
}
