<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use Tests\Support\HttpTestCase;

final class ProductImageTest extends HttpTestCase
{
    private string $uploadPath;

    protected function setUp(): void
    {
        parent::setUp();

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+X2ZkAAAAASUVORK5CYII=',
            true,
        );

        self::assertNotFalse($png);

        $directory = dirname(__DIR__, 3)
            . '/storage/uploads/products';

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $this->uploadPath = $directory
            . '/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png';

        file_put_contents($this->uploadPath, $png);
    }

    protected function tearDown(): void
    {
        if (is_file($this->uploadPath)) {
            unlink($this->uploadPath);
        }

        parent::tearDown();
    }

    public function test_uploaded_product_image_is_served_from_storage(): void
    {
        $response = $this->get(
            '/media/products/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png',
        );

        self::assertSame(200, $this->responseStatus($response));
        self::assertSame(
            'image/png',
            $this->responseHeader($response, 'Content-Type'),
        );
        self::assertNotSame('', $this->responseContent($response));
    }

    public function test_rejects_path_traversal_in_media_filename(): void
    {
        $response = $this->get('/media/products/../.env');

        self::assertSame(404, $this->responseStatus($response));
    }

    public function test_catalogue_uses_public_image_urls(): void
    {
        $this->loginAsUser();

        $html = $this->responseContent($this->get('/'));

        self::assertStringContainsString('<img', $html);
        self::assertStringNotContainsString(
            'src="storage/uploads/',
            $html,
        );
    }
}
