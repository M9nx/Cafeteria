<?php

declare(strict_types=1);

namespace Cafeteria\Validation;

use Cafeteria\DTO\ChecksFilter;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

final class ChecksFilterValidator
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly DateTimeZone $timezone,
    ) {
    }

    /** @return array<string, string> */
    public function validate(ChecksFilter $filter): array
    {
        $errors = [];

        if ($filter->userId !== null && $filter->userId < 1) {
            $errors['user_id'] = 'User ID must be valid.';
        } elseif ($filter->userId !== null && !$this->userExists($filter->userId)) {
            $errors['user_id'] = 'The selected user does not exist.';
        }

        $from = $this->parseDate($filter->from);
        $to = $this->parseDate($filter->to);

        if ($filter->from !== null && $filter->from !== '' && $from === null) {
            $errors['from'] = 'From date must be in YYYY-MM-DD format.';
        }

        if ($filter->to !== null && $filter->to !== '' && $to === null) {
            $errors['to'] = 'To date must be in YYYY-MM-DD format.';
        }

        if ($from !== null && $to !== null && $from > $to) {
            $errors['date_range'] = 'From date must not be after to date.';
        }

        return $errors;
    }

    private function userExists(int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $userId,
        ]);

        return $statement->fetchColumn() !== false;
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
            || ($errors !== false
                && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            return null;
        }

        return $date;
    }
}
