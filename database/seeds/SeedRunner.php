<?php

declare(strict_types=1);

namespace Cafeteria\Database\Seeds;

use PDO;
use Throwable;

final class SeedRunner
{
    public function __construct(private readonly PDO $connection) {}

    /** @return list<string> */
    public function run(): array
    {
        $seeders = [
            new RoomsSeeder($this->connection),
            new CategoriesSeeder($this->connection),
            new ProductsSeeder($this->connection),
            new UsersSeeder($this->connection),
        ];

        $applied = [];

        try {
            $this->connection->beginTransaction();

            foreach ($seeders as $seeder) {
                $seeder->run();
                $applied[] = $seeder->name();
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $e;
        }

        return $applied;
    }
}
