<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\OrderHistoryFilter;
use DateTimeImmutable;
use DateTimeZone;

final class OrderHistoryValidator
{
    private const MAX_PAGE = 1000000;

    public function __construct(
        private readonly DateTimeZone $timezone,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function validate(OrderHistoryFilter $filter): array
    {
        $errors = [];

        if ($filter->page < 1 || $filter->page > self::MAX_PAGE) {
            $errors['page'] = 'Page number is invalid.';
        }

        $from = $this->parseDate($filter->from);
        $to = $this->parseDate($filter->to);

        if ($filter->from !== null && $from === null) {
            $errors['from'] = 'From date must be in YYYY-MM-DD format.';
        }

        if ($filter->to !== null && $to === null) {
            $errors['to'] = 'To date must be in YYYY-MM-DD format.';
        }

        if ($from !== null && $to !== null && $from > $to) {
            $errors['date_range'] = 'From date must not be after to date.';
        }

        return $errors;
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            trim($value),
            $this->timezone,
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || ($errors !== false && (
                $errors['warning_count'] > 0
                || $errors['error_count'] > 0
            ))
        ) {
            return null;
        }

        return $date;
    }
}