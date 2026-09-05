<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use PDO;

final class CategoriesSeeder
{
    /** @var list<string> */
    private const CATEGORIES = ['Hot Drinks', 'Cold Drinks', 'Snacks', 'Bakery'];

    public function __construct(private readonly PDO $connection) {}

    public function name(): string
    {
        return 'categories';
    }

    public function run(): void
    {
        $sql = <<<'SQL'
            INSERT INTO categories (name, is_active)
            VALUES (:name, 1)
            ON DUPLICATE KEY UPDATE
                is_active = VALUES(is_active),
                updated_at = CURRENT_TIMESTAMP
            SQL;

        $stmt = $this->connection->prepare($sql);

        foreach (self::CATEGORIES as $category) {
            $stmt->execute(['name' => $category]);
        }
    }
}
