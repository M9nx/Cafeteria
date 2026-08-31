<?php

declare(strict_types=1);

namespace Cafeteria\Repositories\Pdo;

use Cafeteria\Domain\Users\User;
use Cafeteria\Repositories\Contracts\AuthUserRepositoryInterface;
use PDO;

final class PdoAuthUserRepository implements AuthUserRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                email,
                password_hash,
                role,
                is_active,
                room_id,
                extension,
                profile_image_path
             FROM users
             WHERE email = :email
               AND is_active = 1
             LIMIT 1'
        );

        $statement->execute([
            'email' => $email,
        ]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        $user = User::fromArray($row);

        return [
            'user' => $user,
            'password_hash' => (string) $row['password_hash'],
        ];
    }
}