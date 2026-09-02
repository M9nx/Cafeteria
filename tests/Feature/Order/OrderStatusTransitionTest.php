<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\Domain\Users\Role;
use Cafeteria\Policies\OrderPolicy;
use Cafeteria\Repositories\Contracts\OrderCommandRepositoryInterface;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Services\OrderStatusService;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OrderStatusTransitionTest extends TestCase
{
    private const ADMIN_ID = 1;
    private const USER_ID = 2;
    private const ORDER_ID = 701;
    private const MISSING_ORDER_ID = 999;

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function validTransitions(): iterable
    {
        yield 'PROCESSING -> OUT_FOR_DELIVERY' => ['PROCESSING', 'OUT_FOR_DELIVERY'];
        yield 'OUT_FOR_DELIVERY -> DONE' => ['OUT_FOR_DELIVERY', 'DONE'];
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function invalidTransitions(): iterable
    {
        yield 'PROCESSING -> DONE (skips a step)' => ['PROCESSING', 'DONE'];
        yield 'DONE -> OUT_FOR_DELIVERY (backwards)' => ['DONE', 'OUT_FOR_DELIVERY'];
        yield 'DONE -> PROCESSING (backwards)' => ['DONE', 'PROCESSING'];
        yield 'OUT_FOR_DELIVERY -> PROCESSING (backwards)' => ['OUT_FOR_DELIVERY', 'PROCESSING'];
        yield 'DONE -> DONE (terminal, no-op)' => ['DONE', 'DONE'];
        yield 'PROCESSING -> CANCELLED (wrong path; use cancel())' => ['PROCESSING', 'CANCELLED'];
    }

    #[DataProvider('validTransitions')]
    public function test_admin_can_apply_valid_transition(string $from, string $to): void
    {
        $orders = $this->ordersRepository($from);
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $service->transition($this->admin(), self::ORDER_ID, $to);

        self::assertSame(
            [self::ORDER_ID, $from, $to, self::ADMIN_ID],
            $commands->lastTransitionCallArgs(),
            'transitionIfCurrent() must be called with order id, from-status, to-status and actor id.'
        );
    }

    public function test_transition_normalizes_lowercase_and_whitespace_input(): void
    {
        $orders = $this->ordersRepository('PROCESSING');
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $service->transition($this->admin(), self::ORDER_ID, "  out_for_delivery \n");

        self::assertSame(
            [self::ORDER_ID, 'PROCESSING', 'OUT_FOR_DELIVERY', self::ADMIN_ID],
            $commands->lastTransitionCallArgs()
        );
    }

    #[DataProvider('invalidTransitions')]
    public function test_invalid_transition_is_rejected_and_status_unchanged(
        string $from,
        string $to,
    ): void {
        $orders = $this->ordersRepository($from);
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You are not allowed to apply this status change.');

        try {
            $service->transition($this->admin(), self::ORDER_ID, $to);
        } finally {
            self::assertNull(
                $commands->lastTransitionCallArgs(),
                "Invalid transition {$from} -> {$to} must never reach the repository write."
            );
        }
    }

    public function test_non_admin_cannot_transition_order(): void
    {
        $orders = $this->ordersRepository('PROCESSING');
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Forbidden.');

        try {
            $service->transition($this->user(), self::ORDER_ID, 'OUT_FOR_DELIVERY');
        } finally {
            self::assertNull(
                $commands->lastTransitionCallArgs(),
                'A non-admin actor must be rejected before any repository write is attempted.'
            );
            self::assertFalse(
                $orders->wasQueried(),
                'A non-admin actor must be rejected before the order is even read.'
            );
        }
    }

    public function test_transitioning_a_missing_order_reports_not_found(): void
    {
        $orders = $this->ordersRepository('PROCESSING');
        $commands = $this->commandsRepository();
        $service = $this->makeService($orders, $commands);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Order not found.');

        $service->transition($this->admin(), self::MISSING_ORDER_ID, 'OUT_FOR_DELIVERY');
    }
    public function test_repository_race_condition_surfaces_as_invalid_argument(): void
    {
        $orders = $this->ordersRepository('PROCESSING');
        $commands = $this->commandsRepository();
        $commands->failNextTransition();

        $service = $this->makeService($orders, $commands);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The order status has already changed. Please refresh and try again.'
        );

        $service->transition($this->admin(), self::ORDER_ID, 'OUT_FOR_DELIVERY');
    }

    private function makeService(
        OrderQueryRepositoryInterface $orders,
        OrderCommandRepositoryInterface $commands,
    ): OrderStatusService {
        return new OrderStatusService($orders, $commands, new OrderPolicy());
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

    private function user(): AuthenticatedUser
    {
        return new AuthenticatedUser(
            self::USER_ID,
            'user@example.test',
            'Demo User',
            Role::fromString('USER'),
        );
    }

    private function ordersRepository(string $currentStatus): FakeOrderQueryRepositoryForTransition
    {
        return new FakeOrderQueryRepositoryForTransition([
            self::ORDER_ID => [
                'id' => self::ORDER_ID,
                'user_id' => self::USER_ID,
                'status' => $currentStatus,
            ],
        ]);
    }

    private function commandsRepository(): FakeOrderCommandRepositoryForTransition
    {
        return new FakeOrderCommandRepositoryForTransition();
    }
}

final class FakeOrderQueryRepositoryForTransition implements OrderQueryRepositoryInterface
{
    private bool $queried = false;

    /**
     * @param array<int, array<string, mixed>> $orders
     */
    public function __construct(private array $orders)
    {
    }

    public function findLatestForUser(int $userId): ?array
    {
        return null;
    }

    public function paginateForUser(
        int $userId,
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        int $page = 1,
        int $perPage = 15
    ): array {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function findOwnedDetail(int $orderId, int $userId): ?array
    {
        return null;
    }

    public function findDetailForAdmin(int $orderId): ?array
    {
        $this->queried = true;

        return $this->orders[$orderId] ?? null;
    }

    public function listCurrentQueue(int $page = 1, int $perPage = 15): array
    {
        return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
    }

    public function wasQueried(): bool
    {
        return $this->queried;
    }
}

final class FakeOrderCommandRepositoryForTransition implements OrderCommandRepositoryInterface
{
    private bool $failNextTransition = false;

    /** @var array{0: int, 1: string, 2: string, 3: int}|null */
    private ?array $lastTransitionCallArgs = null;

    public function insertOrder(
        int $userId,
        int $createdByUserId,
        int $roomId,
        ?string $notes,
        string $totalAmount,
    ): int {
        return 0;
    }

    public function insertItems(int $orderId, array $items): void
    {
    }

    public function cancelIfProcessing(
        int $orderId,
        int $changedByUserId,
        DateTimeImmutable $cancelledAt,
    ): bool {
        return false;
    }

    public function transitionIfCurrent(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        int $changedByUserId,
        DateTimeImmutable $changedAt,
    ): bool {
        $this->lastTransitionCallArgs = [$orderId, $fromStatus, $toStatus, $changedByUserId];

        if ($this->failNextTransition) {
            $this->failNextTransition = false;

            return false;
        }

        return true;
    }

    public function failNextTransition(): void
    {
        $this->failNextTransition = true;
    }

    /**
     * @return array{0: int, 1: string, 2: string, 3: int}|null
     */
    public function lastTransitionCallArgs(): ?array
    {
        return $this->lastTransitionCallArgs;
    }
}