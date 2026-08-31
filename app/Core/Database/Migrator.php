<?php

declare(strict_types=1);

namespace Cafeteria\Core\Database;

use PDO;
use RuntimeException;
use Throwable;

final class Migrator
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $migrationDirectory
    ) {
    }

    /**
     * @return list<string>
     */
    public function up(): array
    {
        $this->ensureMigrationTable();

        if (
            !is_dir($this->migrationDirectory)
            || !is_readable($this->migrationDirectory)
        ) {
            throw new RuntimeException(
                "Migration directory is missing or unreadable: {$this->migrationDirectory}"
            );
        }

        $pattern = rtrim(
            $this->migrationDirectory,
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR . '*.sql';

        $files = glob($pattern);

        if ($files === false) {
            throw new RuntimeException('Unable to read the migration directory.');
        }

        sort($files, SORT_STRING);

        $appliedNow = [];

        foreach ($files as $file) {
            $name = basename($file);

            if (!preg_match('/^\d{3}_[a-z0-9_]+\.sql$/', $name)) {
                throw new RuntimeException(
                    "Invalid migration filename: {$name}"
                );
            }

            $checksum = hash_file('sha256', $file);

            if ($checksum === false) {
                throw new RuntimeException(
                    "Unable to hash migration: {$name}"
                );
            }

            $existingChecksum = $this->appliedChecksum($name);

            if ($existingChecksum !== null) {
                if (!hash_equals($existingChecksum, $checksum)) {
                    throw new RuntimeException(
                        "Applied migration was modified: {$name}. "
                        . 'Create a new migration instead.'
                    );
                }

                continue;
            }

            $sql = file_get_contents($file);

            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException(
                    "Migration is empty or unreadable: {$name}"
                );
            }

            try {
                $result = $this->connection->exec($sql);

                if ($result === false) {
                    throw new RuntimeException(
                        "Database rejected migration: {$name}"
                    );
                }

                $statement = $this->connection->prepare(
                    'INSERT INTO schema_migrations '
                    . '(migration, checksum) '
                    . 'VALUES (:migration, :checksum)'
                );

                $statement->execute([
                    'migration' => $name,
                    'checksum' => $checksum,
                ]);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    "Failed to apply migration: {$name}",
                    0,
                    $exception
                );
            }

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
            ) ENGINE=InnoDB
              DEFAULT CHARSET=utf8mb4
              COLLATE=utf8mb4_0900_ai_ci
            SQL
        );
    }

    private function appliedChecksum(string $migration): ?string
    {
        $statement = $this->connection->prepare(
            'SELECT checksum
             FROM schema_migrations
             WHERE migration = :migration'
        );

        $statement->execute([
            'migration' => $migration,
        ]);

        $checksum = $statement->fetchColumn();

        return $checksum === false ? null : (string) $checksum;
    }
}