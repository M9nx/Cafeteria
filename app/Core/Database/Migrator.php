<?php

declare(strict_types=1);

namespace Cafeteria\Core\Database;

use PDO;
use RuntimeException;

final class Migrator
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $migrationDirectory
    ) {
    }

    /** @return list<string> */
    public function up(): array
    {
        $this->ensureMigrationTable();

        $files = glob(rtrim($this->migrationDirectory, '/') . '/*.sql');

        if ($files === false) {
            throw new RuntimeException('Unable to read the migration directory.'); }

        sort($files, SORT_STRING);
        $appliedNow = [];

        foreach ($files as $file) {
            $name = basename($file);

            if (!preg_match('/^\d{3}_[a-z0-9_]+\.sql$/', $name)) {
                throw new RuntimeException("Invalid migration filename: {$name}");
            }

            $checksum = hash_file('sha256', $file);

            if ($checksum === false) {
                throw new RuntimeException("Unable to hash migration: {$name}");
            }

            $existingChecksum = $this->appliedChecksum($name);

            if ($existingChecksum !== null) {
                if (!hash_equals($existingChecksum, $checksum)) {
                    throw new RuntimeException(
                        "Applied migration was modified: {$name}. Create a new migration instead."
                    );
                }

                continue;
            }

            $sql = file_get_contents($file);

            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException("Migration is empty or unreadable: {$name}");
            }

            $this->connection->exec($sql);$statement = $this->connection->prepare(
                'INSERT INTO schema_migrations (migration, checksum) VALUES (:migration, :checksum)'
            );
            $statement->execute([
                'migration' => $name,
                'checksum' => $checksum,
            ]);

            $appliedNow[] = $name;
        }

        return $appliedNow;
    }

    private function ensureMigrationTable(): void
    {
        $this->connection->exec(
            <<<'SQL'
            CREATE TABLE IF NOT EXISTS schema_migrations (
                migration VARCHAR(255) NOT NULL,
                checksum CHAR(64) NOT NULL,
                applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL
        );
    }

    private function appliedChecksum(string $migration): ?string
    {
        $statement = $this->connection->prepare(
            'SELECT checksum FROM schema_migrations WHERE migration = :migration'
        );
        $statement->execute(['migration' => $migration]);
        $checksum = $statement->fetchColumn(); return $checksum === false ? null : (string) $checksum;
    }
}
