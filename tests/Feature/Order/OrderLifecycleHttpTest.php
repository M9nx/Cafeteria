<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Core\Auth\CsrfTokenManager;
use PDO;
use Tests\Support\HttpTestCase;
use Tests\Support\LifecycleOrdersFixture;
use Throwable;

final class OrderLifecycleHttpTest extends HttpTestCase
{
    private PDO $pdo;

    /** @var list<int> */
    private array $seededOrderIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $app = require dirname(__DIR__, 3) . '/bootstrap/app.php';
            $this->pdo = $app['pdo'];
            $this->seededOrderIds = LifecycleOrdersFixture::seedMysql(
                $this->pdo,
                ['processing_order', 'other_users_processing_order'],
            );
        } catch (Throwable $exception) {
            self::markTestSkipped(
                'MySQL test database is not reachable: ' . $exception->getMessage()
            );
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            LifecycleOrdersFixture::deleteMysqlOrders(
                $this->pdo,
                $this->seededOrderIds,
            );
        }

        parent::tearDown();
    }

    public function test_user_gets_not_found_for_another_users_order_detail(): void
    {
        $this->loginAsUser();

        $otherOrderId = LifecycleOrdersFixture::id('other_users_processing_order');
        $response = $this->get('/orders/' . $otherOrderId);

        self::assertSame(404, $this->responseStatus($response));
        self::assertStringContainsString(
            'Order not found',
            $this->responseContent($response),
        );
    }

    public function test_user_cannot_cancel_another_users_order_via_http(): void
    {
        $this->loginAsUser();

        $otherOrderId = LifecycleOrdersFixture::id('other_users_processing_order');
        $response = $this->post('/orders/' . $otherOrderId . '/cancel', [
            CsrfTokenManager::FIELD_NAME => $this->csrfToken(),
        ]);

        self::assertSame(302, $this->responseStatus($response));
        self::assertSame(
            '/orders/' . $otherOrderId,
            $this->responseHeader($response, 'Location'),
        );

        $detail = $this->get('/orders/' . $otherOrderId);
        self::assertSame(404, $this->responseStatus($detail));
    }

    public function test_regular_user_is_forbidden_from_admin_order_queue(): void
    {
        $this->loginAsUser();

        $response = $this->get('/admin/orders');

        self::assertSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Forbidden',
            $this->responseContent($response),
        );
    }

    public function test_admin_can_access_current_order_queue(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/orders');

        self::assertNotSame(403, $this->responseStatus($response));
        self::assertStringContainsString(
            'Current order queue',
            $this->responseContent($response),
        );
    }
}
