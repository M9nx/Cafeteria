<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\User;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\View\View;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Services\OrderService;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class OrderController
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly ProductRepositoryInterface $products,
        private readonly PDO $pdo,
        private readonly CsrfTokenManager $csrf,
    ) {
    }

    public function create(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        return View::render(
            'user.orders.form',
            $this->formData($user, [], []),
            'layouts.app',
        );
    }

    public function store(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $this->verifyCsrf($request);

        $data = $request->body();
        $orderRequest = PlaceOrderRequest::fromArray($data);

        try {
            $this->orders->place($user, $orderRequest);

            return Response::redirect('/');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return View::render(
                'user.orders.form',
                $this->formData(
                    $user,
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
        AuthenticatedUser $user,
        array $errors,
        array $old,
    ): array {
        return [
            'title' => 'New order',
            'currentUser' => $user,
            'products' => $this->products->paginateAvailable(1, 100),
            'rooms' => $this->listActiveRooms(),
            'errors' => $errors,
            'old' => $old,
        ];
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

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rooms[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
            ];
        }

        return $rooms;
    }

    private function verifyCsrf(Request $request): void
    {
        $token = $request->input(CsrfTokenManager::FIELD_NAME);

        if (!$this->csrf->validate(is_string($token) ? $token : null)) {
            throw new RuntimeException('Invalid CSRF token.');
        }
    }
}
