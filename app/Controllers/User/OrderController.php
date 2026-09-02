<?php

declare(strict_types=1);

namespace Cafeteria\Controllers\User;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Core\Auth\CsrfTokenManager;
use Cafeteria\Core\Http\Request;
use Cafeteria\Core\Http\Response;
use Cafeteria\Core\Session\FlashBag;
use Cafeteria\Core\View\View;
use Cafeteria\DTO\OrderHistoryFilter;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Policies\OrderPolicy;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Services\OrderService;
use Cafeteria\Services\OrderStatusService;
use Cafeteria\Services\UserOrderQueryService;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class OrderController
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly OrderStatusService $orderStatus,
        private readonly UserOrderQueryService $userOrderQueries,
        private readonly OrderQueryRepositoryInterface $orderQueries,
        private readonly ProductRepositoryInterface $products,
        private readonly OrderPolicy $orderPolicy,
        private readonly PDO $pdo,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
    ) {
    }

    public function index(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        $from = (string) $request->input('from', '');
        $to = (string) $request->input('to', '');
        $page = max(1, (int) $request->input('page', 1));
        $errors = [];

        try {
            $orders = $this->userOrderQueries->getUserWithOrders(
                $user->id(),
                new OrderHistoryFilter(
                    from: $from !== '' ? $from : null,
                    to: $to !== '' ? $to : null,
                    page: $page,
                ),
                $user,
            );
        } catch (InvalidArgumentException $exception) {
            $orders = [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => 15,
            ];
            $errors = [$exception->getMessage()];
        }

        return View::render(
            'user.orders.index',
            [
                'title' => 'My orders',
                'currentUser' => $user,
                'orders' => $orders,
                'filters' => [
                    'from' => $from,
                    'to' => $to,
                ],
                'errors' => $errors,
                'flashMessages' => $this->flash->pullAll(),
            ],
            'layouts.app',
        );
    }

    public function show(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $order = $user->isAdmin()
            ? $this->orderQueries->findDetailForAdmin($id)
            : $this->orderQueries->findOwnedDetail($id, $user->id());

        if ($order === null) {
            return Response::html('Order not found.', 404);
        }

        $ownerUserId = (int) ($order['user_id'] ?? 0);

        if (!$this->orderPolicy->canViewOrder($user, $ownerUserId)) {
            return Response::html('Forbidden.', 403);
        }

        return View::render(
            'user.orders.show',
            [
                'title' => 'Order #' . $id,
                'currentUser' => $user,
                'order' => $order,
                'canCancel' => $this->orderPolicy->canCancelOrder(
                    $user,
                    $ownerUserId,
                    (string) ($order['status'] ?? ''),
                ),
                'flashMessages' => $this->flash->pullAll(),
            ],
            'layouts.app',
        );
    }

    public function cancel(
        Request $request,
        AuthenticatedUser $user,
        int $id
    ): Response {
        $this->verifyCsrf($request);

        try {
            $this->orderStatus->cancel($user, $id);
            $this->flash->flash('success', 'Order cancelled successfully.');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->flash->flash('error', $exception->getMessage());
        }

        return Response::redirect('/orders/' . $id);
    }

    public function create(
        Request $request,
        AuthenticatedUser $user
    ): Response {
        return View::render(
            'user.orders.create',
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

            return Response::redirect('/?ordered=1');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return View::render(
                'user.orders.create',
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
