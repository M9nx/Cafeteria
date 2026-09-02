<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\Admin;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\View\View;
use Cafeteria\DTO\PlaceOrderOnBehalfRequest;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Services\OrderService;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class AdminOrderController
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly ProductRepositoryInterface $products,
        private readonly PDO $pdo,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function create(
        Request $request,
        AuthenticatedUser $admin,
    ): Response {
        return View::render(
            'admin.orders.create',
            $this->formData(
                $admin,
                [],
                [],
            ),
            'layouts.app',
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $admin,
    ): Response {
        $this->verifyCsrf($request);

        $data = $request->body();

        $orderRequest = PlaceOrderOnBehalfRequest::fromArray(
            $data
        );

        try {
            $orderId = $this->orders->placeOnBehalf(
                $admin,
                $orderRequest,
            );

            $this->flash->flash(
                'success',
                'Order created successfully on behalf of the selected customer.'
            );

            return Response::redirect(
                '/admin/orders'
            );
        } catch (
            InvalidArgumentException |
            RuntimeException $exception
        ) {
            return View::render(
                'admin.orders.create',
                $this->formData(
                    $admin,
                    [$exception->getMessage()],
                    $data,
                ),
                'layouts.app',
            );
        }
    }

    /**
     * @param list<string> $errors
     * @param array<string, mixed> $old
     *
     * @return array<string, mixed>
     */
    private function formData(
        AuthenticatedUser $admin,
        array $errors,
        array $old,
    ): array {
        return [
            'title' => 'Create order for customer',
            'currentUser' => $admin,
            'products' => $this->products->paginateAvailable(
                1,
                100,
            ),
            'rooms' => $this->listActiveRooms(),
            'users' => $this->listActiveCustomers(),
            'errors' => $errors,
            'old' => $old,
        ];
    }

    /**
     * @return list<array{id: int, name: string, email: string}>
     */
    private function listActiveCustomers(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name, email
             FROM users
             WHERE role = \'USER\'
               AND is_active = 1
             ORDER BY name ASC, id ASC'
        );

        if ($statement === false) {
            return [];
        }

        $users = [];

        foreach (
            $statement->fetchAll(PDO::FETCH_ASSOC)
            as $row
        ) {
            $users[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'email' => (string) $row['email'],
            ];
        }

        return $users;
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function listActiveRooms(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name
             FROM rooms
             WHERE is_active = 1
             ORDER BY name ASC, id ASC'
        );

        if ($statement === false) {
            return [];
        }

        $rooms = [];

        foreach (
            $statement->fetchAll(PDO::FETCH_ASSOC)
            as $row
        ) {
            $rooms[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
            ];
        }

        return $rooms;
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input(
            CsrfTokenManager::FIELD_NAME
        );

        if (
            !$this->csrf->validate(
                is_string($token) ? $token : null
            )
        ) {
            throw new RuntimeException(
                'Invalid CSRF token.'
            );
        }
    }
}