<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\DTO\PlaceOrderOnBehalfRequest;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Services\OrderService;
use Cafeteria\Validation\PlaceOrderOnBehalfValidator;
use Cafeteria\Validation\PlaceOrderValidator;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

final class AdminOnBehalfOrderTest extends TestCase
{
    private const ADMIN_ID = 1;
    private const ACTIVE_CUSTOMER_ID = 2;
    private const INACTIVE_CUSTOMER_ID = 3;
    private const ACTIVE_ROOM_ID = 1;
    private const INACTIVE_ROOM_ID = 2;
    private const AVAILABLE_PRODUCT_ID = 10;

    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = $this->freshDatabase();
    }

    public function test_admin_can_place_order_on_behalf_of_active_customer(): void
    {
        $commands = new RecordingOrderCommandRepository();
        $service = $this->makeService($commands);

        $orderId = $service->placeOnBehalf(
            $this->admin(),
            $this->onBehalfRequest(self::ACTIVE_CUSTOMER_ID, self::ACTIVE_ROOM_ID),
        );

        self::assertSame(9001, $orderId);

        $call = $commands->lastInsertOrderCallArgs();
        self::assertNotNull($call);
        self::assertSame(
            self::ACTIVE_CUSTOMER_ID,
            $call['user_id'],
            'The order must be recorded under the selected customer, not the admin.'
        );
        self::assertSame(
            self::ADMIN_ID,
            $call['created_by_user_id'],
            'The order must record the acting admin as the creator.'
        );
        self::assertNotSame(
            $call['user_id'],
            $call['created_by_user_id'],
            'Customer and creator must be distinct for an on-behalf order.'
        );
    }

    public function test_non_admin_cannot_place_order_on_behalf(): void
    {
        $commands = new RecordingOrderCommandRepository();
        $service = $this->makeService($commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Only administrators can place orders on behalf of users.'
        );

        try {
            $service->placeOnBehalf(
                $this->user(self::ACTIVE_CUSTOMER_ID), // an authenticated but non-admin actor
                $this->onBehalfRequest(self::ACTIVE_CUSTOMER_ID, self::ACTIVE_ROOM_ID),
            );
        } finally {
            self::assertNull(
                $commands->lastInsertOrderCallArgs(),
                'No order may be persisted when the actor is not an admin.'
            );
        }
    }

    public function test_missing_customer_id_is_rejected(): void
    {
        $commands = new RecordingOrderCommandRepository();
        $service = $this->makeService($commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please select a valid customer.');

        try {
            $service->placeOnBehalf(
                $this->admin(),
                $this->onBehalfRequest(0, self::ACTIVE_ROOM_ID),
            );
        } finally {
            self::assertNull($commands->lastInsertOrderCallArgs());
        }
    }

    public function test_inactive_customer_is_rejected(): void
    {
        $commands = new RecordingOrderCommandRepository();
        $service = $this->makeService($commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The selected customer does not exist or is inactive.');

        try {
            $service->placeOnBehalf(
                $this->admin(),
                $this->onBehalfRequest(self::INACTIVE_CUSTOMER_ID, self::ACTIVE_ROOM_ID),
            );
        } finally {
            self::assertNull($commands->lastInsertOrderCallArgs());
        }
    }

    public function test_nonexistent_customer_is_rejected(): void
    {
        $commands = new RecordingOrderCommandRepository();
        $service = $this->makeService($commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The selected customer does not exist or is inactive.');

        try {
            $service->placeOnBehalf(
                $this->admin(),
                $this->onBehalfRequest(999999, self::ACTIVE_ROOM_ID),
            );
        } finally {
            self::assertNull($commands->lastInsertOrderCallArgs());
        }
    }

    public function test_inactive_room_is_rejected(): void
    {
        $commands = new RecordingOrderCommandRepository();
        $service = $this->makeService($commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please select a valid active room.');

        try {
            $service->placeOnBehalf(
                $this->admin(),
                $this->onBehalfRequest(self::ACTIVE_CUSTOMER_ID, self::INACTIVE_ROOM_ID),
            );
        } finally {
            self::assertNull($commands->lastInsertOrderCallArgs());
        }
    }

    private function makeService(OrderCommandRepositoryInterface $commands): OrderService
    {
        return new OrderService(
            new FakeProductRepositoryForOnBehalf([
                self::AVAILABLE_PRODUCT_ID => ['name' => 'Grilled Cheese', 'price' => '15.00'],
            ]),
            $commands,
            new PlaceOrderValidator(),
            new PlaceOrderOnBehalfValidator($this->pdo, new PlaceOrderValidator()),
            $this->pdo,
        );
    }

    private function onBehalfRequest(int $customerId, int $roomId): PlaceOrderOnBehalfRequest
    {
        return PlaceOrderOnBehalfRequest::fromArray([
            'user_id' => $customerId,
            'room_id' => $roomId,
            'notes' => 'Please deliver to front desk.',
            'items' => [
                ['product_id' => self::AVAILABLE_PRODUCT_ID, 'quantity' => 2],
            ],
        ]);
    }

    private function admin(): AuthenticatedUser
    {
        return new AuthenticatedUser(
            self::ADMIN_ID,
            'admin@example.test',
            'Demo Admin',
            Role::fromString('ADMIN'),
        );
    }

    private function user(int $id): AuthenticatedUser
    {
        return new AuthenticatedUser(
            $id,
            'notadmin@example.test',
            'Not An Admin',
            Role::fromString('USER'),
        );
    }

    private function freshDatabase(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('
            CREATE TABLE rooms (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1
            )
        ');

        $pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT "USER",
                is_active INTEGER NOT NULL DEFAULT 1
            )
        ');

        $pdo->exec(sprintf(
            "INSERT INTO rooms (id, name, is_active) VALUES (%d, 'Active Room', 1), (%d, 'Inactive Room', 0)",
            self::ACTIVE_ROOM_ID,
            self::INACTIVE_ROOM_ID,
        ));

        $pdo->exec(sprintf(
            "INSERT INTO users (id, name, email, role, is_active) VALUES
                (%d, 'Demo Admin', 'admin@example.test', 'ADMIN', 1),
                (%d, 'Active Customer', 'customer@example.test', 'USER', 1),
                (%d, 'Inactive Customer', 'inactive@example.test', 'USER', 0)",
            self::ADMIN_ID,
            self::ACTIVE_CUSTOMER_ID,
            self::INACTIVE_CUSTOMER_ID,
        ));

        return $pdo;
    }
}

final class FakeProductRepositoryForOnBehalf implements ProductRepositoryInterface
{
    /**
     * @param array<int, array{name: string, price: string}> $products
     */
    public function __construct(private array $products)
    {
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function paginateAvailable(int $page = 1, int $perPage = 15, ?int $categoryId = null): array
    {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function findById(int $id): ?array
    {
        return $this->products[$id] ?? null;
    }

    public function findAvailableByIds(array $ids): array
    {
        $items = [];

        foreach ($ids as $id) {
            if (isset($this->products[$id])) {
                $items[$id] = $this->products[$id];
            }
        }

        return $items;
    }

    public function create(array $attributes): int
    {
        return 0;
    }

    public function update(int $id, array $attributes): bool
    {
        return false;
    }

    public function updateProduct(int $id, array $values): void
    {
    }

    public function softDelete(int $id): bool
    {
        return false;
    }
}

final class RecordingOrderCommandRepository implements OrderCommandRepositoryInterface
{
    /**
     * @var array{
     *     user_id: int,
     *     created_by_user_id: int,
     *     room_id: int,
     *     notes: ?string,
     *     total_amount: string
     * }|null
     */
    private ?array $lastInsertOrderCallArgs = null;

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        $this->lastInsertOrderCallArgs = [
            'user_id' => $userId,
            'created_by_user_id' => $createdByUserId,
            'room_id' => $roomId,
            'notes' => $notes,
            'total_amount' => $totalAmount,
        ];

        return 9001;
    }

    public function insertItems(int $orderId, array $items): void
    {
    }

    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        \DateTimeImmutable $cancelledAt,
    ): bool {
        return false;
    }

    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        \DateTimeImmutable $changedAt,
    ): bool {
        return false;
    }

    /**
     * @return array{
     *     user_id: int,
     *     created_by_user_id: int,
     *     room_id: int,
     *     notes: ?string,
     *     total_amount: string
     * }|null
     */
    public function lastInsertOrderCallArgs(): ?array
    {
        return $this->lastInsertOrderCallArgs;
    }
}