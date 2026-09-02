<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\Core\Auth\AuthenticatedUser;
use Cafeteria\DTO\OrderHistoryFilter;
use Cafeteria\Repositories\Contracts\OrderQueryRepositoryInterface;
use Cafeteria\Validation\OrderHistoryValidator;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

final class UserOrderQueryService
{
    private const PER_PAGE = 15;

    public function __construct(
        private readonly OrderQueryRepositoryInterface $orders,
        private readonly OrderHistoryValidator $validator,
        private readonly DateTimeZone $timezone,
    ) {
    }

    /**
     * @return array{
     *     items: array<int, array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int
     * }
     */
    public function getUserWithOrders(
        int $userId,
        OrderHistoryFilter $filter,
        AuthenticatedUser $actor,
    ): array {
        if ($userId < 1) {
            throw new InvalidArgumentException('User ID must be valid.');
        }

        if (!$actor->isAdmin() && $actor->id() !== $userId) {
            throw new RuntimeException('Forbidden.');
        }

        $errors = $this->validator->validate($filter);

        if ($errors !== []) {
            throw new InvalidArgumentException(
                implode(' ', array_values($errors))
            );
        }

        return $this->orders->paginateForUser(
            $userId,
            $this->parseDate($filter->from),
            $this->parseEndDate($filter->to),
            $filter->page,
            self::PER_PAGE,
        );
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            trim($value),
            $this->timezone,
        ) ?: null;
    }

    private function parseEndDate(?string $value): ?DateTimeImmutable
    {
        $date = $this->parseDate($value);

        return $date?->setTime(23, 59, 59);
    }
}
