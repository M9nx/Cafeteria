<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Repositories\Contracts\ReportRepositoryInterface;
use Cafeteria\Validation\ChecksFilterValidator;
use InvalidArgumentException;

final class ReportQueryService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly ChecksFilterValidator $validator,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summarize(ChecksFilter $filter): array
    {
        $errors = $this->validator->validate($filter);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_values($errors))
            );
        }

        return $this->reports->summarize([
            'from' => $filter->from,
            'to' => $filter->to,
            'user_id' => $filter->userId,
            'include_cancelled' => $filter->includeCancelled,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ordersForUser(
        int $userId,
        ChecksFilter $filter,
    ): array {
        if ($userId < 1) {
            throw new InvalidArgumentException('User ID must be valid.');
        }

        $errors = $this->validator->validate($filter);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_values($errors))
            );
        }

        return $this->reports->ordersForUser($userId, [
            'from' => $filter->from,
            'to' => $filter->to,
            'include_cancelled' => $filter->includeCancelled,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function orderDetailsForReport(
        int $orderId,
        ChecksFilter $filter,
    ): ?array {
        if ($orderId < 1) {
            throw new InvalidArgumentException('Order ID must be valid.');
        }

        $errors = $this->validator->validate($filter);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_values($errors))
            );
        }

        return $this->reports->orderDetailsForReport($orderId, [
            'from' => $filter->from,
            'to' => $filter->to,
            'include_cancelled' => $filter->includeCancelled,
        ]);
    }

    /**
     * @return array{
     *     user: array{id: int, name: string, email: string},
     *     orders: list<array<string, mixed>>,
     *     summary: array{order_count: int, total_amount: string}
     * }
     */
    public function drillDown(int $userId, ChecksFilter $filter): array
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('User ID must be valid.');
        }

        $userErrors = $this->validator->validate(
            new ChecksFilter(userId: $userId)
        );

        if ($userErrors !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_values($userErrors))
            );
        }

        $errors = $this->validator->validate($filter);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_values($errors))
            );
        }

        $user = $this->reports->findReportUser($userId);

        if ($user === null) {
            throw new InvalidArgumentException(
                'The selected user does not exist.'
            );
        }

        $orders = $this->reports->ordersForUser($userId, [
            'from' => $filter->from,
            'to' => $filter->to,
            'include_cancelled' => $filter->includeCancelled,
        ]);

        $summary = $this->reports->summarize([
            'from' => $filter->from,
            'to' => $filter->to,
            'user_id' => $userId,
            'include_cancelled' => $filter->includeCancelled,
        ]);

        $userRow = $summary['users'][0] ?? [
            'order_count' => 0,
            'total_amount' => '0.00',
        ];

        return [
            'user' => [
                'id' => (int) $user['id'],
                'name' => (string) $user['name'],
                'email' => (string) $user['email'],
            ],
            'orders' => $orders,
            'summary' => [
                'order_count' => (int) ($userRow['order_count'] ?? 0),
                'total_amount' => (string) ($userRow['total_amount'] ?? '0.00'),
            ],
        ];
    }
}
