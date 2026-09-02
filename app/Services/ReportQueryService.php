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
}
