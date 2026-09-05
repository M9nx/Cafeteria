<?php

declare(strict_types=1);

namespace Tests\Support;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\DTO\PlaceOrderRequest;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\ProductRepositoryInterface;
use Cafeteria\Services\OrderService;
use Cafeteria\Validation\PlaceOrderOnBehalfValidator;
use Cafeteria\Validation\PlaceOrderValidator;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class OrderFeatureTestCase extends TestCase
{
    /** @var array<string, mixed> */
    protected array $ordersFixture;

    /** @var array<string, mixed> */
    protected array $productsFixture;

    /** @var array<string, mixed> */
    protected array $usersFixture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ordersFixture = require dirname(__DIR__) . '/Fixtures/orders.php';
        $this->productsFixture = require dirname(__DIR__) . '/Fixtures/products.php';
        $this->usersFixture = require dirname(__DIR__) . '/Fixtures/users.php';
    }

    protected function placeRequestFromFixture(string $key): PlaceOrderRequest
    {
        $payload = $this->ordersFixture[$key];

        return PlaceOrderRequest::fromArray([
            'room_id' => $payload['room_id'] ?? 1,
            'notes' => $payload['notes'] ?? null,
            'items' => $payload['items'] ?? [],
            'total' => $payload['total'] ?? null,
        ]);
    }

    /**
     * @return array<int, array{name: string, price: string}>
     */
    protected function availableCatalogProducts(): array
    {
        $products = [];

        foreach ($this->productsFixture['available'] as $product) {
            $products[(int) $product['id']] = [
                'name' => (string) $product['name'],
                'price' => (string) $product['price'],
            ];
        }

        return $products;
    }

    protected function makeService(
        ?ProductRepositoryInterface $products = null,
        ?OrderCommandRepositoryInterface $orders = null,
    ): OrderService {
        $pdo = $this->sqliteWithRoom();

        return new OrderService(
            $products ?? new FeatureFakeProductRepository($this->availableCatalogProducts()),
            $orders ?? new FeatureRecordingOrderRepository(),
            new PlaceOrderValidator(),
            new PlaceOrderOnBehalfValidator(
                $pdo,
                new PlaceOrderValidator(),
            ),
            $pdo,
        );
    }

    protected function demoUser(): AuthenticatedUser
    {
        $user = $this->usersFixture['user'];

        return new AuthenticatedUser(
            (int) $user['id'],
            (string) $user['email'],
            (string) $user['name'],
            Role::fromString((string) $user['role']),
        );
    }

    protected function sqliteWithRoom(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE rooms (id INTEGER PRIMARY KEY)');
        $pdo->exec('INSERT INTO rooms (id) VALUES (1)');

        return $pdo;
    }
}

final class FeatureFakeProductRepository implements ProductRepositoryInterface
{
    /**
     * @param array<int, array{name: string, price: string}> $products
     */
    public function __construct(
        private array $products,
    ) {
    }

    /**
     * @param array{name?: string, price?: string} $values
     */
    public function updateProduct(int $id, array $values): void
    {
        if (!isset($this->products[$id])) {
            return;
        }

        $this->products[$id] = array_merge($this->products[$id], $values);
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

    public function softDelete(int $id): bool
    {
        return false;
    }
}

class FeatureRecordingOrderRepository implements OrderCommandRepositoryInterface
{
    private ?string $lastTotal = null;

    /** @var list<array<string, mixed>> */
    private array $lastItems = [];

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        $this->lastTotal = $totalAmount;

        return 101;
    }

    public function insertItems(int $orderId, array $items): void
    {
        $this->lastItems = $items;
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

    public function lastTotal(): ?string
    {
        return $this->lastTotal;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function lastItems(): array
    {
        return $this->lastItems;
    }
}

final class FeatureFailingOrderRepository extends FeatureRecordingOrderRepository
{
    public function insertItems(int $orderId, array $items): void
    {
        throw new \RuntimeException('Simulated item insert failure');
    }
}
