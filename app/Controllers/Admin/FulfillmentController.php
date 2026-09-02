<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\View\View;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Services\OrderStatusService;
use InvalidArgumentException;
use RuntimeException;

final class FulfillmentController
{
    use RendersAdminView;

    public function __construct(
        private readonly OrderQueryRepositoryInterface $orders,
        private readonly OrderStatusService $orderStatus,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function current(
        Request $request,
        AuthenticatedUser $admin
    ): Response {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));

        return $this->renderAdmin(
            $admin,
            'admin.orders.queue',
            'Current order queue',
            [
                'queue' => $this->orders->listCurrentQueue($page, $perPage),
                'flashMessages' => $this->flash->pullAll(),
            ],
        );
    }

    public function updateStatus(
        Request $request,
        AuthenticatedUser $admin,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        $nextStatus = (string) $request->input('status', '');

        try {
            $this->orderStatus->transition($admin, $id, $nextStatus);
            $this->flash->flash('success', 'Order status updated successfully.');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->flash->flash('error', $exception->getMessage());
        }

        return Response::redirect('/admin/orders/current');
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input(CsrfTokenManager::FIELD_NAME);

        if (!$this->csrf->validate(is_string($token) ? $token : null)) {
            throw new RuntimeException('Invalid CSRF token.');
        }
    }
}
